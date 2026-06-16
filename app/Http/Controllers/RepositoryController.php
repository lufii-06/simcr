<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\User;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class RepositoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Repository::with('project')->orderBy('created_at', 'desc');

        if ($user->role === 'developer') {
            $query->whereHas('project.developers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'client') {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('client_id', $user->client->id ?? 0);
            });
        }

        $repositories = $query->get();

        return view('pages.repository.index', compact('repositories'));
    }

    public function toggleStatus(Repository $repository)
    {
        $user = auth()->user();
        if ($user->id !== ($repository->project?->user_id ?? 0) && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $newStatus = $repository->status === 'active' ? 'inactive' : 'active';
        $repository->update([
            'status' => $newStatus,
        ]);

        return back()->with('success', 'Repository status updated to '.$newStatus);
    }

    public function toggleVisibility(Repository $repository)
    {
        $user = auth()->user();
        if ($user->id !== ($repository->project?->user_id ?? 0) && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $isPublic = $repository->is_public;
        $repository->update([
            'is_public' => ! $isPublic,
        ]);
        if ($repository->is_public == 1) {
            $repository->update([
                'access_token' => null,
            ]);
        }

        return back()->with('success', 'Repository visibility updated to '.($isPublic ? 'private' : 'public').'.');
    }

    public function generateToken(Repository $repository)
    {
        $user = auth()->user();
        if ($user->id !== ($repository->project?->user_id ?? 0) && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($repository->is_public) {
            $repository->update([
                'access_token' => null,
            ]);

            return back()->with('error', 'Repository is public, no access token needed.');
        }
        $token = \Illuminate\Support\Str::random(40);
        $repository->update([
            'access_token' => $token,
        ]);

        return back()->with('success', 'New access token generated successfully.');
    }

    public function viewFile(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $path = $request->input('path');
        $branch = $request->input('branch', $repository->default_branch);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return response()->json(['error' => 'Repository not found'], 404);
        }

        $res = $this->runGitCommand($repoPath, 'show '.escapeshellarg($branch).':'.escapeshellarg($path));

        if (! $res['success']) {
            return response()->json(['error' => 'Could not read file content'], 400);
        }

        return response()->json([
            'name' => basename($path),
            'content' => implode("\n", $res['output']),
            'path' => $path,
            'branch' => $branch,
        ]);
    }

    public function downloadFile(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $path = $request->input('path');
        $branch = $request->input('branch', $repository->default_branch);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            abort(404);
        }

        $fileName = basename($path);

        return response()->streamDownload(function () use ($repoPath, $branch, $path) {
            $cmd = 'cd '.escapeshellarg($repoPath).' && git show '.escapeshellarg($branch).':'.escapeshellarg($path);
            $handle = popen($cmd, 'r');
            while (! feof($handle)) {
                echo fread($handle, 8192);
            }
            pclose($handle);
        }, $fileName);
    }

    public function downloadArchive(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $branch = $request->input('branch', $repository->default_branch ?? 'main');
        $format = $request->input('format', 'zip'); // zip or tar.gz

        if ($format !== 'zip' && $format !== 'tar.gz') {
            abort(400, 'Invalid format');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            abort(404, 'Repository physical folder not found');
        }

        $ext = $format === 'zip' ? 'zip' : 'tar.gz';
        $fileName = $repository->name.'-'.$branch.'.'.$ext;
        $formatArg = $format === 'zip' ? 'zip' : 'tar.gz';

        return response()->streamDownload(function () use ($repoPath, $branch, $formatArg) {
            $cmd = 'git --git-dir='.escapeshellarg($repoPath).
                ' archive --format='.escapeshellarg($formatArg).
                ' '.escapeshellarg($branch).' 2>NUL';

            $handle = popen($cmd, 'r');
            if ($handle) {
                while (! feof($handle)) {
                    echo fread($handle, 8192);
                }
                pclose($handle);
            }
        }, $fileName, [
            'Content-Type' => $format === 'zip' ? 'application/zip' : 'application/x-gzip',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    public function createBranch(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client') {
            return back()->with('error', 'Clients are not allowed to create branches.');
        }

        $request->validate([
            'branch_name' => 'required|string|max:100',
            'from_branch' => 'required|string|max:100',
        ]);

        $newBranch = trim($request->input('branch_name'));
        $fromBranch = trim($request->input('from_branch'));

        if (preg_match('/[^a-zA-Z0-9_\-\.\/]/', $newBranch) || str_starts_with($newBranch, '-') || str_ends_with($newBranch, '/')) {
            return back()->with('error', 'Invalid branch name. Use alphanumeric characters, dashes, underscores, dots, or slashes.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);

        if (! in_array($fromBranch, $branches)) {
            return back()->with('error', 'Source branch does not exist.');
        }

        if (in_array($newBranch, $branches)) {
            return back()->with('error', 'Branch name already exists.');
        }

        $cmd = 'branch '.escapeshellarg($newBranch).' '.escapeshellarg($fromBranch);
        $res = $this->runGitCommand($repoPath, $cmd);

        if ($res['success']) {
            return redirect()->route('repository.show', [
                'repository' => $repository->name,
                'branch' => $newBranch,
            ])->with('success', "Branch '{$newBranch}' created successfully from '{$fromBranch}'.");
        }

        return back()->with('error', 'Failed to create branch. Ensure the repository has at least one commit.');
    }

    public function createTag(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client') {
            return back()->with('error', 'Clients are not allowed to create tags.');
        }

        $request->validate([
            'tag_name' => 'required|string|max:100',
            'target' => 'required|string|max:100',
            'message' => 'nullable|string|max:255',
        ]);

        $tagName = trim($request->input('tag_name'));
        $target = trim($request->input('target'));
        $message = trim($request->input('message', ''));

        if (preg_match('/[^a-zA-Z0-9_\-\.\/]/', $tagName) || str_starts_with($tagName, '-') || str_ends_with($tagName, '/')) {
            return back()->with('error', 'Invalid tag name. Use alphanumeric characters, dashes, underscores, dots, or slashes.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $tags = $this->showGetTags($repoPath);
        if (in_array($tagName, $tags)) {
            return back()->with('error', 'Tag name already exists.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);
        if (! in_array($target, $branches)) {
            $revParseCmd = 'rev-parse --verify '.escapeshellarg($target);
            $revRes = $this->runGitCommand($repoPath, $revParseCmd);
            if (! $revRes['success']) {
                return back()->with('error', 'Target branch or commit does not exist.');
            }
        }

        if ($message !== '') {
            $cmd = 'tag -a '.escapeshellarg($tagName).' -m '.escapeshellarg($message).' '.escapeshellarg($target);
        } else {
            $cmd = 'tag '.escapeshellarg($tagName).' '.escapeshellarg($target);
        }

        $res = $this->runGitCommand($repoPath, $cmd);

        if ($res['success']) {
            return redirect()->route('repository.show', [
                'repository' => $repository->name,
                'tab' => 'tags',
            ])->with('success', "Tag '{$tagName}' created successfully pointing to '{$target}'.");
        }

        return back()->with('error', 'Failed to create tag. Ensure the repository has at least one commit.');
    }

    public function mergeRebase(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client') {
            return back()->with('error', 'Clients are not allowed to merge or rebase branches.');
        }

        $request->validate([
            'action_type' => 'required|in:merge,rebase',
            'source_branch' => 'required|string|max:100',
            'target_branch' => 'required|string|max:100',
        ]);

        $actionType = $request->input('action_type');
        $sourceBranch = trim($request->input('source_branch'));
        $targetBranch = trim($request->input('target_branch'));

        if ($sourceBranch === $targetBranch) {
            return back()->with('error', 'Source and target branch cannot be the same.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);
        if (! in_array($sourceBranch, $branches) || ! in_array($targetBranch, $branches)) {
            return back()->with('error', 'One or both of the specified branches do not exist.');
        }

        // Clone to a temporary directory
        $tempPath = storage_path('app/temp_git_op_'.uniqid());
        try {
            \File::makeDirectory($tempPath, 0755, true);

            // Clone bare repository to temporary directory
            $output = [];
            $result = 0;
            exec('git clone '.escapeshellarg($repoPath).' '.escapeshellarg($tempPath), $output, $result);
            if ($result !== 0) {
                throw new \Exception('Failed to clone repository to temporary folder.');
            }

            // Configure temporary Git user
            exec('git -C '.escapeshellarg($tempPath).' config user.name "SIMCR System"');
            exec('git -C '.escapeshellarg($tempPath).' config user.email "system@simcr.com"');

            if ($actionType === 'merge') {
                // Checkout target branch
                exec('git -C '.escapeshellarg($tempPath).' checkout '.escapeshellarg($targetBranch), $output, $result);
                if ($result !== 0) {
                    throw new \Exception("Failed to checkout target branch '{$targetBranch}'.");
                }

                // Merge source branch into target
                exec('git -C '.escapeshellarg($tempPath).' merge '.escapeshellarg($sourceBranch), $output, $result);
                if ($result !== 0) {
                    // Conflict occurred! Abort merge and direct user to local
                    exec('git -C '.escapeshellarg($tempPath).' merge --abort');

                    return back()->with('error', "Conflict detected! Gagal menggabungkan cabang '{$sourceBranch}' ke '{$targetBranch}' secara otomatis. Silakan lakukan merge secara manual di repositori lokal Anda, selesaikan konflik, lalu push kembali.");
                }

                // Push back to bare repository
                exec('git -C '.escapeshellarg($tempPath).' push origin '.escapeshellarg($targetBranch), $output, $result);
                if ($result !== 0) {
                    throw new \Exception('Failed to push merged changes back to the repository.');
                }

                $message = "Branch '{$sourceBranch}' successfully merged into '{$targetBranch}'.";
                $redirectBranch = $targetBranch;

            } else {
                // Rebase source branch onto target branch
                // Checkout source branch (the one being rebased)
                exec('git -C '.escapeshellarg($tempPath).' checkout '.escapeshellarg($sourceBranch), $output, $result);
                if ($result !== 0) {
                    throw new \Exception("Failed to checkout source branch '{$sourceBranch}'.");
                }

                // Rebase onto target branch
                exec('git -C '.escapeshellarg($tempPath).' rebase '.escapeshellarg($targetBranch), $output, $result);
                if ($result !== 0) {
                    // Conflict occurred! Abort rebase and direct user to local
                    exec('git -C '.escapeshellarg($tempPath).' rebase --abort');

                    return back()->with('error', "Conflict detected! Gagal melakukan rebase cabang '{$sourceBranch}' di atas '{$targetBranch}' secara otomatis. Silakan lakukan rebase secara manual di repositori lokal Anda, selesaikan konflik, lalu push kembali.");
                }

                // Push back to bare repository (must force push because rebase rewrites commits history)
                exec('git -C '.escapeshellarg($tempPath).' push -f origin '.escapeshellarg($sourceBranch), $output, $result);
                if ($result !== 0) {
                    throw new \Exception('Failed to push rebased changes back to the repository.');
                }

                $message = "Branch '{$sourceBranch}' successfully rebased onto '{$targetBranch}'.";
                $redirectBranch = $sourceBranch;
            }

            return redirect()->route('repository.show', [
                'repository' => $repository->name,
                'branch' => $redirectBranch,
            ])->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Git operation failed: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memproses aksi Git: '.$e->getMessage());
        } finally {
            if (\File::exists($tempPath)) {
                \File::deleteDirectory($tempPath);
            }
        }
    }

    public function deleteBranch(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client') {
            return back()->with('error', 'Clients are not allowed to delete branches.');
        }

        $request->validate([
            'branch_name' => 'required|string|max:100',
        ]);

        $branchName = trim($request->input('branch_name'));

        if ($branchName === $repository->default_branch) {
            return back()->with('error', 'Cannot delete the default branch.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);
        if (! in_array($branchName, $branches)) {
            return back()->with('error', 'Branch does not exist.');
        }

        $cmd = 'branch -D '.escapeshellarg($branchName);
        $res = $this->runGitCommand($repoPath, $cmd);

        if ($res['success']) {
            return back()->with('success', "Branch '{$branchName}' has been successfully deleted.");
        }

        return back()->with('error', 'Failed to delete branch.');
    }

    public function deleteTag(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client') {
            return back()->with('error', 'Clients are not allowed to delete tags.');
        }

        $request->validate([
            'tag_name' => 'required|string|max:100',
        ]);

        $tagName = trim($request->input('tag_name'));

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $tags = $this->showGetTags($repoPath);
        if (! in_array($tagName, $tags)) {
            return back()->with('error', 'Tag does not exist.');
        }

        $cmd = 'tag -d '.escapeshellarg($tagName);
        $res = $this->runGitCommand($repoPath, $cmd);

        if ($res['success']) {
            return back()->with('success', "Tag '{$tagName}' has been successfully deleted.");
        }

        return back()->with('error', 'Failed to delete tag.');
    }

    public function commitDetail(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $request->validate([
            'hash' => 'required|string|min:7|max:40',
        ]);

        $hash = $request->input('hash');

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return response()->json(['error' => 'Repository physical folder not found.'], 404);
        }

        // Run git show command to fetch full details & diff
        $cmd = 'show '.escapeshellarg($hash);
        $res = $this->runGitCommand($repoPath, $cmd);

        if (! $res['success']) {
            return response()->json(['error' => 'Failed to retrieve commit details.'], 500);
        }

        $lines = $res['output'];
        $author = '';
        $date = '';
        $message = '';
        $diffs = [];
        $currentFile = null;
        $inMessage = false;

        foreach ($lines as $line) {
            // Find Author
            if (strpos($line, 'Author: ') === 0) {
                $author = substr($line, 8);
                $inMessage = false;

                continue;
            }
            // Find Date
            if (strpos($line, 'Date: ') === 0) {
                $date = substr($line, 6);
                $inMessage = true;

                continue;
            }
            // Parse Commit Message
            if ($inMessage) {
                if (strpos($line, 'diff --git') === 0) {
                    $inMessage = false;
                } else {
                    // Lines in git commit message output are usually indented by 4 spaces
                    $message .= (strpos($line, '    ') === 0 ? substr($line, 4) : $line)."\n";

                    continue;
                }
            }

            // Parse Diffs
            if (strpos($line, 'diff --git') === 0) {
                if ($currentFile) {
                    $diffs[] = $currentFile;
                }
                $filename = '';
                if (preg_match('/b\/(.+)$/', $line, $m)) {
                    $filename = $m[1];
                }
                $currentFile = [
                    'filename' => $filename,
                    'lines' => [],
                ];

                continue;
            }

            if ($currentFile) {
                if (strpos($line, '--- a/') === 0 || strpos($line, '+++ b/') === 0 || strpos($line, 'index ') === 0 || strpos($line, 'new file mode ') === 0 || strpos($line, 'deleted file mode ') === 0) {
                    continue; // skip headers
                }

                $type = 'normal';
                if (strpos($line, '+') === 0) {
                    $type = 'addition';
                } elseif (strpos($line, '-') === 0) {
                    $type = 'deletion';
                } elseif (strpos($line, '@@') === 0) {
                    $type = 'info';
                }

                $currentFile['lines'][] = [
                    'type' => $type,
                    'content' => $line,
                ];
            }
        }
        if ($currentFile) {
            $diffs[] = $currentFile;
        }

        return response()->json([
            'hash' => $hash,
            'author' => trim($author),
            'date' => trim($date),
            'message' => trim($message),
            'diffs' => $diffs,
        ]);
    }

    public function compareBranches(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $request->validate([
            'source' => 'required|string|max:100',
            'target' => 'required|string|max:100',
        ]);

        $source = trim($request->input('source'));
        $target = trim($request->input('target'));

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return response()->json(['error' => 'Repository physical folder not found.'], 404);
        }

        // Get ahead count (commits in source but not in target)
        $cmdAhead = 'rev-list --count '.escapeshellarg($target).'..'.escapeshellarg($source);
        $resAhead = $this->runGitCommand($repoPath, $cmdAhead);
        $aheadCount = $resAhead['success'] ? (int) trim(implode('', $resAhead['output'])) : 0;

        // Get behind count (commits in target but not in source)
        $cmdBehind = 'rev-list --count '.escapeshellarg($source).'..'.escapeshellarg($target);
        $resBehind = $this->runGitCommand($repoPath, $cmdBehind);
        $behindCount = $resBehind['success'] ? (int) trim(implode('', $resBehind['output'])) : 0;

        // Get commits in source but not in target
        $cmdCommits = 'log '.escapeshellarg($target).'..'.escapeshellarg($source).' --pretty=format:"%h|%s|%an|%ad" --date=short';
        $resCommits = $this->runGitCommand($repoPath, $cmdCommits);
        $commits = [];
        if ($resCommits['success']) {
            foreach ($resCommits['output'] as $line) {
                $parts = explode('|', $line);
                if (count($parts) === 4) {
                    $commits[] = [
                        'hash' => $parts[0],
                        'message' => $parts[1],
                        'author' => $parts[2],
                        'date' => $parts[3],
                    ];
                }
            }
        }

        // Get code diff
        $cmdDiff = 'diff '.escapeshellarg($target).'..'.escapeshellarg($source);
        $resDiff = $this->runGitCommand($repoPath, $cmdDiff);

        $diffs = [];
        if ($resDiff['success']) {
            $lines = $resDiff['output'];
            $currentFile = null;
            foreach ($lines as $line) {
                if (strpos($line, 'diff --git') === 0) {
                    if ($currentFile) {
                        $diffs[] = $currentFile;
                    }
                    $filename = '';
                    if (preg_match('/b\/(.+)$/', $line, $m)) {
                        $filename = $m[1];
                    }
                    $currentFile = [
                        'filename' => $filename,
                        'lines' => [],
                    ];

                    continue;
                }

                if ($currentFile) {
                    if (strpos($line, '--- a/') === 0 || strpos($line, '+++ b/') === 0 || strpos($line, 'index ') === 0 || strpos($line, 'new file mode ') === 0 || strpos($line, 'deleted file mode ') === 0) {
                        continue;
                    }

                    $type = 'normal';
                    if (strpos($line, '+') === 0) {
                        $type = 'addition';
                    } elseif (strpos($line, '-') === 0) {
                        $type = 'deletion';
                    } elseif (strpos($line, '@@') === 0) {
                        $type = 'info';
                    }

                    $currentFile['lines'][] = [
                        'type' => $type,
                        'content' => $line,
                    ];
                }
            }
            if ($currentFile) {
                $diffs[] = $currentFile;
            }
        }

        return response()->json([
            'source' => $source,
            'target' => $target,
            'ahead_count' => $aheadCount,
            'behind_count' => $behindCount,
            'commits' => $commits,
            'diffs' => $diffs,
        ]);
    }

    public function show(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $repository->load(['project.owner', 'project.developers.user', 'project.developers.role']);

        $this->showAuthorize($repository, $user);

        $selectedBranch = $request->input('branch', $repository->default_branch ?? 'main');
        $path = $request->input('path', '');
        $error = null;
        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        // Initialize default values for null safety
        $branches = [];
        $tags = [];
        $recentCommits = [];
        $files = [];
        $readme = null;
        $languages = [];
        $stats = ['size' => '0', 'files' => '0', 'objects' => '0'];
        $contributionData = ['labels' => [], 'data' => []];
        $activityData = ['labels' => [], 'data' => []];
        $dayActivityData = ['labels' => [], 'data' => []];
        $httpUrl = '';
        $gitGraph = '';
        $pullRequests = [];

        if (file_exists($repoPath)) {
            $branches = $this->showGetBranches($repoPath, $repository);
            $tags = $this->showGetTags($repoPath);
            $recentCommits = $this->showGetRecentCommits($repoPath, $selectedBranch);
            $files = $this->showGetFiles($repoPath, $selectedBranch, $path);
            $readme = $this->showGetReadme($repoPath, $selectedBranch);
            $stats = $this->showGetStats($repoPath, $selectedBranch);
            $contributionData = $this->showGetContributionData($repoPath, $user);
            $activityData = $this->showGetActivityData($repoPath);
            $languages = $this->showGetLanguages($repoPath, $selectedBranch);
            $dayActivityData = $this->showGetDayActivityData($repoPath);
            $httpUrl = $this->showGetHttpUrl($repository);
            $gitGraph = $this->showGetGitGraph($repoPath);
            $pullRequests = $repository->pullRequests()->with('user')->orderBy('created_at', 'desc')->get();
        } else {
            $error = 'Repository physical folder not found. Please ensure the repository has been created correctly on the server.';
        }

        return view('pages.repository.show', compact(
            'repository',
            'branches',
            'tags',
            'recentCommits',
            'selectedBranch',
            'path',
            'stats',
            'contributionData',
            'activityData',
            'dayActivityData',
            'gitGraph',
            'httpUrl',
            'error',
            'files',
            'readme',
            'languages',
            'pullRequests'
        ));
    }

    private function runGitCommand(string $repoPath, string $command): array
    {
        $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
        $fullCommand = 'git --git-dir='.escapeshellarg($repoPath).
            ' '.$command.' 2>'.$nullDevice;

        exec($fullCommand, $output, $result);

        return [
            'success' => $result === 0,
            'output' => $output,
            'command' => $fullCommand,
        ];
    }

    private function showAuthorize(Repository $repository, $user): void
    {
        if ($user->role === 'developer') {
            $isAssigned = $repository->project->developers()->where('user_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($user->role === 'client') {
            if ($repository->project->client_id !== ($user->client->id ?? 0)) {
                abort(403, 'Unauthorized action.');
            }
        }
    }

    private function showGetBranches(string $repoPath, Repository $repository): array
    {
        $res = $this->runGitCommand($repoPath, 'branch');
        $branches = array_map(fn ($b) => trim(str_replace('*', '', $b)), $res['output']);

        if (empty($branches)) {
            $branches = [$repository->default_branch ?? 'main'];
        }

        return $branches;
    }

    private function showGetTags(string $repoPath): array
    {
        $res = $this->runGitCommand($repoPath, 'tag');

        return $res['output'];
    }

    private function showGetRecentCommits(string $repoPath, string $selectedBranch): array
    {
        $recentCommits = [];

        $cmd = 'log '.escapeshellarg($selectedBranch).' -n 10 --pretty=format:"%h|%s|%an|%ad" --date=short';
        $res = $this->runGitCommand($repoPath, $cmd);

        if (! $res['success']) {
            return [];
        }
        foreach ($res['output'] as $line) {
            $parts = explode('|', $line);

            if (count($parts) === 4) {
                $recentCommits[] = [
                    'hash' => $parts[0],
                    'message' => $parts[1],
                    'author' => $parts[2],
                    'date' => $parts[3],
                ];
            }
        }

        return $recentCommits;
    }

    private function showGetFiles(string $repoPath, string $selectedBranch, ?string $path = ''): array
    {
        $files = [];

        // FIX 1: tambahkan trailing slash jika masuk folder
        $cleanPath = $path ? rtrim($path, '/').'/' : '';

        // FIX 2: gunakan path yang sudah dibersihkan
        $pathArg = $cleanPath ? escapeshellarg($cleanPath) : '';

        $cmd = 'ls-tree -l '.escapeshellarg($selectedBranch).' '.$pathArg;
        $res = $this->runGitCommand($repoPath, $cmd);

        if (! $res['success']) {
            return [];
        }
        foreach ($res['output'] as $line) {
            // FIX 3: regex diperbaiki agar lebih stabil
            if (preg_match('/^(\d+)\s+(\w+)\s+([a-f0-9]+)\s+(\d+|-)\t(.+)$/', $line, $matches)) {
                $fullPath = $matches[5];
                $size = trim($matches[4]);

                // FIX 4: ambil nama relatif terhadap folder saat ini
                $relativeName = $cleanPath
                    ? str_replace($cleanPath, '', $fullPath)
                    : $fullPath;

                // FIX 5: skip nested child agar tidak tampil recursive
                if (str_contains($relativeName, '/')) {
                    continue;
                }

                $files[] = [
                    'type' => $matches[2], // blob / tree
                    'size' => $size === '-'
                        ? '-'
                        : round((int) $size / 1024, 2).' KB',
                    'name' => $relativeName,
                    'path' => $fullPath,
                ];
            }
        }

        usort($files, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }

            return $a['type'] === 'tree' ? -1 : 1;
        });

        // FIX 6: parent path pakai cleanPath
        if ($cleanPath) {
            $parts = explode('/', trim($cleanPath, '/'));
            array_pop($parts);
            $parentPath = implode('/', $parts);

            array_unshift($files, [
                'type' => 'tree',
                'size' => '-',
                'name' => '..',
                'path' => $parentPath,
                'is_parent' => true,
            ]);
        }

        return $files;
    }

    private function showGetReadme(string $repoPath, string $selectedBranch): ?string
    {
        $res = $this->runGitCommand($repoPath, 'show '.escapeshellarg($selectedBranch).':README.md');
        if ($res['success'] && ! empty($res['output'])) {
            return implode("\n", $res['output']);
        }

        return null;
    }

    private function showGetContributionData(string $repoPath, $user): array
    {
        $res = $this->runGitCommand($repoPath, 'shortlog -sn --all');

        $currentUserName = $user->name;
        $currentUserCommits = 0;
        $othersCommits = 0;
        foreach ($res['output'] as $line) {
            $line = trim($line);
            if (preg_match('/(\d+)\t(.+)/', $line, $matches)) {
                $count = (int) $matches[1];
                $author = $matches[2];
                if (stripos($author, $currentUserName) !== false) {
                    $currentUserCommits += $count;
                } else {
                    $othersCommits += $count;
                }
            }
        }

        return [
            'labels' => ['You ('.$currentUserName.')', 'Others'],
            'data' => [$currentUserCommits, $othersCommits],
        ];
    }

    private function showGetActivityData(string $repoPath): array
    {
        $res = $this->runGitCommand($repoPath, 'log --all --since="30 days ago" --pretty=format:"%ad" --date=short');
        if (! $res['success']) {
            return ['labels' => [], 'data' => []];
        }

        if (! empty($res['output'])) {
            $historyCounts = array_count_values($res['output']);
            ksort($historyCounts);

            return [
                'labels' => array_keys($historyCounts),
                'data' => array_values($historyCounts),
            ];
        }

        return ['labels' => [], 'data' => []];
    }

    private function showGetStats(string $repoPath, string $selectedBranch): array
    {
        $stats = [
            'size' => '0 MB',
            'files' => '0',
            'last_commit' => '-',
        ];

        /*
        |--------------------------------------------------------------------------
        | 1. Repository Size
        |--------------------------------------------------------------------------
        */
        $totalBytes = 0;
        if (\File::exists($repoPath)) {
            $files = \File::allFiles($repoPath);
            foreach ($files as $file) {
                $totalBytes += $file->getSize();
            }
        }
        $stats['size'] = round($totalBytes / 1024 / 1024, 2).' MB';

        /*
        |--------------------------------------------------------------------------
        | 2. Total Files
        |--------------------------------------------------------------------------
        */
        $resFiles = $this->runGitCommand($repoPath, 'ls-tree -r --name-only '.escapeshellarg($selectedBranch));
        if ($resFiles['success']) {
            $stats['files'] = (string) count($resFiles['output']);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Last Commit
        |--------------------------------------------------------------------------
        */
        $resLastCommit = $this->runGitCommand($repoPath, 'log -1 --format="%h | %an | %ad" --date=short '.escapeshellarg($selectedBranch));
        if ($resLastCommit['success'] && ! empty($resLastCommit['output'])) {
            $stats['last_commit'] = $resLastCommit['output'][0];
        }

        return $stats;
    }

    private function showGetLanguages(string $repoPath, string $selectedBranch): array
    {
        $languages = [];

        $res = $this->runGitCommand($repoPath, 'ls-tree -r --name-only '.escapeshellarg($selectedBranch));
        if (! $res['success']) {
            return [];
        }

        $langCounts = [];
        $totalCount = 0;

        $langMap = [
            'php' => ['name' => 'PHP', 'color' => '#4F5D95'],
            'js' => ['name' => 'JavaScript', 'color' => '#f1e05a'],
            'css' => ['name' => 'CSS', 'color' => '#563d7c'],
            'html' => ['name' => 'HTML', 'color' => '#e34c26'],
            'blade.php' => ['name' => 'Blade', 'color' => '#ff2d20'],
            'sql' => ['name' => 'SQL', 'color' => '#e38c00'],
            'json' => ['name' => 'JSON', 'color' => '#292929'],
            'md' => ['name' => 'Markdown', 'color' => '#083fa1'],
            'yml' => ['name' => 'YAML', 'color' => '#cb171e'],
            'yaml' => ['name' => 'YAML', 'color' => '#cb171e'],
            'vue' => ['name' => 'Vue', 'color' => '#41b883'],
            'ts' => ['name' => 'TypeScript', 'color' => '#3178c6'],
        ];

        foreach ($res['output'] as $file) {
            // FIX 3: skip file tanpa extension
            if (! str_contains($file, '.')) {
                continue;
            }

            // FIX 4: prioritas khusus blade.php
            if (str_ends_with($file, '.blade.php')) {
                $ext = 'blade.php';
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            }

            if (isset($langMap[$ext])) {
                $langCounts[$ext] = ($langCounts[$ext] ?? 0) + 1;
                $totalCount++;
            }
        }

        // FIX 5: hindari divide by zero
        if ($totalCount <= 0) {
            return [];
        }

        foreach ($langCounts as $ext => $count) {
            $languages[] = [
                'name' => $langMap[$ext]['name'],
                'color' => $langMap[$ext]['color'],
                'percent' => round(($count / $totalCount) * 100, 1),
                'count' => $count,
            ];
        }

        usort($languages, fn ($a, $b) => $b['percent'] <=> $a['percent']);

        return $languages;
    }

    private function showGetDayActivityData(string $repoPath): array
    {
        $res = $this->runGitCommand($repoPath, 'log --all --pretty=format:"%ad" --date=format:"%A"');
        $dayCounts = [
            'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0,
            'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0,
        ];
        foreach ($res['output'] as $day) {
            if (isset($dayCounts[$day])) {
                $dayCounts[$day]++;
            }
        }

        return [
            'labels' => array_keys($dayCounts),
            'data' => array_values($dayCounts),
        ];
    }

    private function showGetHttpUrl(Repository $repository): string
    {
        $httpRoot = request()->getSchemeAndHttpHost();
        $tokenPart = ! ($repository->is_public) && $repository->access_token
            ? $repository->access_token.'@'
            : '';
        $cleanHttpRoot = str_replace(['http://', 'https://'], '', $httpRoot);
        $protocol = request()->getScheme();

        return "{$protocol}://{$tokenPart}{$cleanHttpRoot}/repository/{$repository->name}.git";
    }

    private function showGetGitGraph(string $repoPath): string
    {
        $res = $this->runGitCommand($repoPath, 'log --graph --oneline --all --decorate --color=never');

        return implode("\n", $res['output']);
    }

    private function gitAuthenticate(Request $request, Repository $repository): bool
    {
        if ($repository->is_public) {
            return true;
        }

        $username = $request->getUser();
        $password = $request->getPassword();
        $token = $request->query('token');

        // Mode 1: Token Authentication (via Basic Auth Username or Query Parameter)
        if (($username && $username === $repository->access_token) || ($token && $token === $repository->access_token)) {
            return true;
        }

        // Mode 2: User Credentials Authentication (Email & Password)
        if ($username && $password) {
            $user = User::where('email', $username)->first();
            if ($user && Hash::check($password, $user->password)) {
                // Check if user is authorized to access this repository's project
                if ($user->role === 'pm' || $user->role === 'owner') {
                    return true;
                }

                if ($user->role === 'developer') {
                    $isAssigned = $repository->project->developers()->where('user_id', $user->id)->exists();
                    if ($isAssigned) {
                        return true;
                    }
                }

                if ($user->role === 'client') {
                    if ($repository->project->client_id === ($user->client->id ?? 0)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function gitInfoRefs(Request $request, $repositoryName)
    {
        if (class_exists(Debugbar::class)) {
            Debugbar::disable();
        }

        $repository = Repository::where('name', $repositoryName)->first();
        if (! $repository) {
            return response('Repository not found', 404);
        }

        // Authenticate using the dual-mode helper
        if (! $this->gitAuthenticate($request, $repository)) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Git Access"',
            ]);
        }

        $service = $request->query('service');
        if ($service !== 'git-upload-pack' && $service !== 'git-receive-pack') {
            return response('Unsupported service', 400);
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repositoryName.'.git';

        if (! file_exists($repoPath)) {
            return response('Repository physical folder not found', 404);
        }

        // Response format for Smart HTTP Protocol
        $packet = '# service='.$service."\n";
        $len = strlen($packet) + 4;
        $firstLine = sprintf('%04x', $len).$packet.'0000';

        // Execute git upload-pack or receive-pack in advertise-refs mode
        $gitService = str_replace('git-', '', $service);
        $gitCmd = 'git '.$gitService.' --stateless-rpc --advertise-refs '.escapeshellarg($repoPath);

        $output = shell_exec($gitCmd);

        return response($firstLine.$output)
            ->header('Content-Type', 'application/x-'.$service.'-advertisement')
            ->header('Cache-Control', 'no-cache, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1980 00:00:00 GMT');
    }

    public function gitServiceRpc(Request $request, $repositoryName, $service)
    {
        if (class_exists(Debugbar::class)) {
            Debugbar::disable();
        }

        if ($service !== 'git-upload-pack' && $service !== 'git-receive-pack') {
            return response('Unsupported service', 400);
        }

        $repository = Repository::where('name', $repositoryName)->first();
        if (! $repository) {
            return response('Repository not found', 404);
        }

        // Authenticate using the dual-mode helper
        if (! $this->gitAuthenticate($request, $repository)) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Git Access"',
            ]);
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repositoryName.'.git';

        if (! file_exists($repoPath)) {
            return response('Repository physical folder not found', 404);
        }

        // Run git service in stateless-rpc mode, piping request raw body to git's stdin
        $gitService = str_replace('git-', '', $service);
        $gitCmd = "git {$gitService} --stateless-rpc ".escapeshellarg($repoPath);

        // We use proc_open to stream the request input to git, and stream the git output back to client
        $descriptorSpec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $input = $request->getContent();
        $process = proc_open($gitCmd, $descriptorSpec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);

            if ($service === 'git-receive-pack') {
                // Parse the ref updates that were in the push
                $updates = [];
                if (preg_match_all('/([0-9a-f]{40})\s+([0-9a-f]{40})\s+(refs\/[^\s\0\n]+)/', $input, $refMatches, PREG_SET_ORDER)) {
                    foreach ($refMatches as $m) {
                        $updates[] = ['old' => $m[1], 'new' => $m[2], 'ref' => $m[3]];
                    }
                }

                // Resolve pushing user from Basic Auth
                $username = $request->getUser();
                $pushingUser = $username ? User::where('email', $username)->first() : null;

                // Validate commit messages NOW that objects are written to the repo
                $violation = $this->validatePushCommitMessages($updates, $repoPath, $repository, $pushingUser);

                if ($violation) {
                    // Rollback: revert all updated refs back to their old values
                    foreach ($updates as $upd) {
                        if ($upd['new'] === '0000000000000000000000000000000000000000') {
                            continue; // was a deletion, skip
                        }
                        if ($upd['old'] === '0000000000000000000000000000000000000000') {
                            // New branch was created — delete it
                            $this->runGitCommand($repoPath, 'update-ref -d '.escapeshellarg($upd['ref']));
                        } else {
                            // Existing branch was updated — revert to old hash
                            $this->runGitCommand($repoPath, 'update-ref '.escapeshellarg($upd['ref']).' '.escapeshellarg($upd['old']));
                        }
                    }

                    // Build a pkt-line error response so git client prints the error
                    $errorMsg = 'ERR '.$violation;
                    $pktLen = strlen($errorMsg) + 4;
                    $pktLine = sprintf('%04x', $pktLen).$errorMsg.'0000';

                    return response($pktLine, 200)
                        ->header('Content-Type', 'application/x-git-receive-pack-result');
                }

                // Valid — run side effects (auto-toggle checklists)
                try {
                    $this->processPushedCommits($input, $repoPath, $repository);
                } catch (\Exception $e) {
                    \Log::error('Failed to process pushed commits: '.$e->getMessage());
                }
            }

            return response($output)
                ->header('Content-Type', 'application/x-'.$service.'-result')
                ->header('Cache-Control', 'no-cache, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1980 00:00:00 GMT');
        }

        return response('Internal Server Error', 500);
    }

    /**
     * Validate all commit messages in a push against the SIMCR commit standard.
     * Called AFTER git has written objects to the repo so git log can read them.
     * Format (with checklist): [feat|fix] : message [TASK-XXX] [CK-XXX] [FINISH|UNFINISH]
     * Format (without checklist): [feat|fix] : message [TASK-XXX]
     * Returns an error string if invalid, or null if all commits are valid.
     */
    private function validatePushCommitMessages(array $updates, string $repoPath, Repository $repository, ?User $pushingUser): ?string
    {
        $fullPattern = '/^\[(feat|fix)\]\s*:\s*.+\[(TASK-[A-Z0-9-]+)\]\s*\[(CK-[A-Z0-9-]+)\]\s*\[(FINISH|UNFINISH)\]$/i';
        $shortPattern = '/^\[(feat|fix)\]\s*:\s*.+\[(TASK-[A-Z0-9-]+)\]$/i';

        foreach ($updates as $update) {
            if ($update['new'] === '0000000000000000000000000000000000000000') {
                continue; // Skip deletions
            }

            if ($update['old'] === '0000000000000000000000000000000000000000') {
                // First push to a new branch: list all commits reachable from new but not from any other refs
                $gitLogCmd = 'log '.escapeshellarg($update['new']).' --not --branches --tags --format='.escapeshellarg('%H|%ae|%s[COMMIT_DELIMITER]');
            } else {
                $gitLogCmd = 'log '.escapeshellarg($update['old'].'..'.$update['new']).' --format='.escapeshellarg('%H|%ae|%s[COMMIT_DELIMITER]');
            }

            $res = $this->runGitCommand($repoPath, $gitLogCmd);

            if (! $res['success'] || empty($res['output'])) {
                \Log::warning('[GitValidate] git log returned empty or failed — validation skipped for this ref', ['ref' => $update['ref']]);

                continue;
            }

            $rawCommits = implode("\n", $res['output']);
            $commits = explode('[COMMIT_DELIMITER]', $rawCommits);

            foreach ($commits as $commitRaw) {
               
                $commitRaw = trim($commitRaw);
                if (empty($commitRaw)) {
                    continue;
                }

                $parts = explode('|', $commitRaw, 3);
                if (count($parts) < 3) {
                    continue;
                }

                $hash = trim($parts[0]);
                $message = trim($parts[2]);

                $isFullMatch = preg_match($fullPattern, $message, $fullMatches);
                $isShortMatch = preg_match($shortPattern, $message, $shortMatches);
                 \Log::debug('[GitValidate] git log result', [
                    'message' => $message,
                ]);
                if (! $isFullMatch && ! $isShortMatch) {
                    return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Invalid commit message format.\n"
                        .'Message: "'.$message."\"\n"
                        ."Required format: [feat|fix] : your message [TASK-XXX]\n"
                        .'With checklist : [feat|fix] : your message [TASK-XXX] [CK-XXX] [FINISH|UNFINISH]';
                }

                // Validate TASK code in DB
                $taskCode = strtoupper($isFullMatch ? $fullMatches[2] : $shortMatches[2]);
                $task = \App\Models\Task::where('code', $taskCode)->first();

                if (! $task) {
                    return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Task [{$taskCode}] does not exist in the system.";
                }

                if ($task->project_id !== $repository->project_id) {
                    return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Task [{$taskCode}] does not belong to this repository's project.";
                }

                if ($pushingUser && $task->assigned_to !== null && $task->assigned_to !== $pushingUser->id) {
                    if ($pushingUser->role === 'developer') {
                        return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Task [{$taskCode}] is not assigned to you.";
                    }
                }

                // Validate CK code if present
                if ($isFullMatch) {
                    $ckCode = strtoupper($fullMatches[3]);
                    $checklist = \App\Models\TaskChecklist::where('code', $ckCode)->first();

                    if (! $checklist) {
                        return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Checklist [{$ckCode}] does not exist in the system.";
                    }

                    if ($checklist->task_id !== $task->id) {
                        return '[SIMCR] Commit '.substr($hash, 0, 7)." rejected: Checklist [{$ckCode}] does not belong to task [{$taskCode}].";
                    }
                }
            }
        }

        return null; // All commits valid
    }

    private function processPushedCommits($input, string $repoPath, Repository $repository)
    {
        $updates = [];
        if (preg_match_all('/([0-9a-f]{40})\s+([0-9a-f]{40})\s+(refs\/[^\s\0\n]+)/', $input, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $updates[] = [
                    'old' => $match[1],
                    'new' => $match[2],
                    'ref' => $match[3],
                ];
            }
        }

        foreach ($updates as $update) {
            if ($update['new'] === '0000000000000000000000000000000000000000') {
                continue; // Skip branch/tag deletion
            }

            if ($update['old'] === '0000000000000000000000000000000000000000') {
                // New branch: get all commits on the new ref that aren't on any other refs
                $gitLogCmd = 'log '.escapeshellarg($update['new']).' --not --all --exclude='.escapeshellarg($update['ref']).' --format='.escapeshellarg('%H|%ae|%B[COMMIT_DELIMITER]');
            } else {
                // Existing branch: get commits between old and new
                $gitLogCmd = 'log '.escapeshellarg($update['old'].'..'.$update['new']).' --format='.escapeshellarg('%H|%ae|%B[COMMIT_DELIMITER]');
            }

            $res = $this->runGitCommand($repoPath, $gitLogCmd);
            if ($res['success'] && ! empty($res['output'])) {
                $rawCommits = implode("\n", $res['output']);
                $commits = explode('[COMMIT_DELIMITER]', $rawCommits);

                foreach ($commits as $commitRaw) {
                    $commitRaw = trim($commitRaw);
                    if (empty($commitRaw)) {
                        continue;
                    }

                    $parts = explode('|', $commitRaw, 3);
                    if (count($parts) < 3) {
                        continue;
                    }

                    $authorEmail = trim($parts[1]);
                    $message = trim($parts[2]);

                    $this->processCommitMessage($message, $authorEmail, $repository);
                }
            }
        }
    }

    private function processCommitMessage(string $message, string $authorEmail, Repository $repository)
    {
        // Full format: [feat|fix] : message [TASK-XXX] [CK-XXX] [FINISH|UNFINISH]
        $fullPattern = '/^\[(feat|fix)\]\s*:\s*(.+)\[(TASK-[A-Z0-9-]+)\]\s*\[(CK-[A-Z0-9-]+)\]\s*\[(FINISH|UNFINISH)\]$/i';

        $user = User::where('email', $authorEmail)->first();
        if (! $user) {
            $user = auth()->user() ?? User::where('role', 'pm')->first();
        }

        if (preg_match($fullPattern, $message, $m)) {
            $taskCode = strtoupper(trim($m[3]));
            $ckCode = strtoupper(trim($m[4]));
            $status = strtoupper(trim($m[5]));
            $isCompleted = ($status === 'FINISH');

            $checklist = \App\Models\TaskChecklist::where('code', $ckCode)->first();
            if ($checklist && $checklist->task->project_id === $repository->project_id) {
                if ($checklist->is_completed !== $isCompleted) {
                    $checklist->update(['is_completed' => $isCompleted]);

                    \App\Models\TaskLog::create([
                        'task_id' => $checklist->task_id,
                        'user_id' => $user?->id,
                        'action' => 'checklist_toggled',
                        'details' => '[Auto-Commit] '.($isCompleted ? 'Checked' : 'Unchecked')
                            ." item: {$checklist->item_text} via commit: \"{$message}\"",
                    ]);
                }
            }
        }
    }

    public function mergeRequestsCreate(Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);

        return view('pages.repository.merge_requests.create', compact('repository', 'branches'));
    }

    public function mergeRequestsStore(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_branch' => 'required|string|max:100',
            'target_branch' => 'required|string|max:100',
        ]);

        $source = trim($request->input('source_branch'));
        $target = trim($request->input('target_branch'));
        $title = trim($request->input('title'));
        $description = trim($request->input('description'));

        if ($source === $target) {
            return back()->withInput()->with('error', 'Source branch and target branch cannot be the same.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);
        if (! in_array($source, $branches) || ! in_array($target, $branches)) {
            return back()->withInput()->with('error', 'One or both branches do not exist in the repository.');
        }

        // Check if there is already an open merge request with the same source and target branches
        $existing = $repository->pullRequests()
            ->where('source_branch', $source)
            ->where('target_branch', $target)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'An open Merge Request already exists for these branches.');
        }

        $pullRequest = $repository->pullRequests()->create([
            'user_id' => $user->id,
            'title' => $title,
            'description' => $description,
            'source_branch' => $source,
            'target_branch' => $target,
            'status' => 'open',
        ]);

        // Notify project owner
        $owner = $repository->project?->owner;
        if ($owner && $owner->id !== $user->id) {
            $owner->notify(new \App\Notifications\MergeRequestNotification($repository, $pullRequest, 'created'));
        }

        return redirect()->route('repository.show', [
            'repository' => $repository->name,
            'tab' => 'pills-pulls',
        ])->with('success', 'Merge Request successfully created.');
    }

    public function mergeRequestsShow(Repository $repository, \App\Models\PullRequest $pullRequest)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($pullRequest->repository_id !== $repository->id) {
            abort(404);
        }

        $pullRequest->load('user');

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        $commits = [];
        $diffs = [];
        $error = null;

        if (file_exists($repoPath)) {
            // Get commits between target and source
            $cmdCommits = 'log '.escapeshellarg($pullRequest->target_branch).'..'.escapeshellarg($pullRequest->source_branch).' --pretty=format:"%h|%s|%an|%ad" --date=short';
            $resCommits = $this->runGitCommand($repoPath, $cmdCommits);
            if ($resCommits['success']) {
                foreach ($resCommits['output'] as $line) {
                    $parts = explode('|', $line);
                    if (count($parts) === 4) {
                        $commits[] = [
                            'hash' => $parts[0],
                            'message' => $parts[1],
                            'author' => $parts[2],
                            'date' => $parts[3],
                        ];
                    }
                }
            }

            // Get code diff between target and source
            $cmdDiff = 'diff '.escapeshellarg($pullRequest->target_branch).'..'.escapeshellarg($pullRequest->source_branch);
            $resDiff = $this->runGitCommand($repoPath, $cmdDiff);
            if ($resDiff['success']) {
                $lines = $resDiff['output'];
                $currentFile = null;

                foreach ($lines as $line) {
                    if (strpos($line, 'diff --git') === 0) {
                        if ($currentFile) {
                            $diffs[] = $currentFile;
                        }
                        $filename = '';
                        if (preg_match('/b\/(.+)$/', $line, $matches)) {
                            $filename = $matches[1];
                        }
                        $currentFile = [
                            'filename' => $filename,
                            'lines' => [],
                        ];
                    } elseif ($currentFile) {
                        $type = 'normal';
                        if (strpos($line, '+') === 0 && strpos($line, '+++') !== 0) {
                            $type = 'addition';
                        } elseif (strpos($line, '-') === 0 && strpos($line, '---') !== 0) {
                            $type = 'deletion';
                        } elseif (strpos($line, '@@') === 0) {
                            $type = 'info';
                        }

                        $currentFile['lines'][] = [
                            'type' => $type,
                            'content' => $line,
                        ];
                    }
                }
                if ($currentFile) {
                    $diffs[] = $currentFile;
                }
            }
        } else {
            $error = 'Repository physical folder not found.';
        }

        return view('pages.repository.merge_requests.show', compact('repository', 'pullRequest', 'commits', 'diffs', 'error'));
    }

    public function mergeRequestsMerge(Repository $repository, \App\Models\PullRequest $pullRequest)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($user->role === 'client' || $user->role === 'developer') {
            return back()->with('error', 'You are not authorized to merge Merge Requests.');
        }

        if ($pullRequest->repository_id !== $repository->id) {
            abort(404);
        }

        if ($pullRequest->status !== 'open') {
            return back()->with('error', 'This Merge Request is not open.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $tempPath = storage_path('app/temp_git_pr_merge_'.uniqid());
        try {
            \File::makeDirectory($tempPath, 0755, true);

            // Clone bare repository to temporary directory
            $output = [];
            $result = 0;
            exec('git clone '.escapeshellarg($repoPath).' '.escapeshellarg($tempPath), $output, $result);
            if ($result !== 0) {
                throw new \Exception('Failed to clone repository.');
            }

            // Configure temporary Git user (attributing to merging user)
            exec('git -C '.escapeshellarg($tempPath).' config user.name '.escapeshellarg($user->name));
            exec('git -C '.escapeshellarg($tempPath).' config user.email '.escapeshellarg($user->email));

            // Checkout target branch
            exec('git -C '.escapeshellarg($tempPath).' checkout '.escapeshellarg($pullRequest->target_branch), $output, $result);
            if ($result !== 0) {
                throw new \Exception("Failed to checkout target branch '{$pullRequest->target_branch}'.");
            }

            // Merge source branch into target
            $mergeMsg = "Merge request #{$pullRequest->id} from {$pullRequest->source_branch}\n\n{$pullRequest->title}";
            exec('git -C '.escapeshellarg($tempPath).' merge '.escapeshellarg($pullRequest->source_branch).' -m '.escapeshellarg($mergeMsg), $output, $result);
            if ($result !== 0) {
                // Conflict detected!
                exec('git -C '.escapeshellarg($tempPath).' merge --abort');

                return back()->with('error', "Conflict detected! Gagal menggabungkan cabang '{$pullRequest->source_branch}' ke '{$pullRequest->target_branch}' secara otomatis. Selesaikan konflik di lokal repositori Anda, selesaikan, lalu push kembali.");
            }

            // Push back to bare repository
            exec('git -C '.escapeshellarg($tempPath).' push origin '.escapeshellarg($pullRequest->target_branch), $output, $result);
            if ($result !== 0) {
                throw new \Exception('Failed to push merged changes back to the repository.');
            }

            // Successfully merged! Update status
            $pullRequest->update(['status' => 'merged']);

            // Notify MR creator
            $mrCreator = $pullRequest->user;
            if ($mrCreator && $mrCreator->id !== $user->id) {
                $mrCreator->notify(new \App\Notifications\MergeRequestNotification($repository, $pullRequest, 'merged'));
            }

            return redirect()->route('repository.merge-requests.show', [$repository->name, $pullRequest->id])
                ->with('success', "Merge Request #{$pullRequest->id} successfully merged!");

        } catch (\Exception $e) {
            \Log::error('Git merge MR failed: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memproses merge: '.$e->getMessage());
        } finally {
            if (\File::exists($tempPath)) {
                \File::deleteDirectory($tempPath);
            }
        }
    }

    public function mergeRequestsClose(Repository $repository, \App\Models\PullRequest $pullRequest)
    {
        $user = auth()->user();
        $this->showAuthorize($repository, $user);

        if ($pullRequest->repository_id !== $repository->id) {
            abort(404);
        }

        if ($pullRequest->status !== 'open') {
            return back()->with('error', 'This Merge Request is not open.');
        }

        // Author of MR, PM, Owner, or Admin can close
        if ($user->id !== $pullRequest->user_id && $user->role !== 'pm' && $user->role !== 'owner' && $user->role !== 'admin') {
            return back()->with('error', 'You are not authorized to close this Merge Request.');
        }

        $pullRequest->update(['status' => 'closed']);

        // Notify MR creator
        $mrCreator = $pullRequest->user;
        if ($mrCreator && $mrCreator->id !== $user->id) {
            $mrCreator->notify(new \App\Notifications\MergeRequestNotification($repository, $pullRequest, 'closed'));
        }

        return redirect()->route('repository.merge-requests.show', [$repository->name, $pullRequest->id])
            ->with('success', "Merge Request #{$pullRequest->id} has been closed.");
    }
}
