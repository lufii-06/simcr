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
        $newStatus = $repository->status === 'active' ? 'inactive' : 'active';
        $repository->update([
            'status' => $newStatus,
        ]);

        return back()->with('success', 'Repository status updated to '.$newStatus);
    }

    public function toggleVisibility(Repository $repository)
    {
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

        if (!file_exists($repoPath)) {
            return back()->with('error', 'Repository physical folder not found.');
        }

        $branches = $this->showGetBranches($repoPath, $repository);

        if (!in_array($fromBranch, $branches)) {
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
                'branch' => $newBranch
            ])->with('success', "Branch '{$newBranch}' created successfully from '{$fromBranch}'.");
        }

        return back()->with('error', 'Failed to create branch. Ensure the repository has at least one commit.');
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
            'languages'
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

        $process = proc_open($gitCmd, $descriptorSpec, $pipes);

        if (is_resource($process)) {
            // Write request raw content to stdin
            $input = $request->getContent();
            fwrite($pipes[0], $input);
            fclose($pipes[0]);

            // Read output from stdout
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Read error from stderr
            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);

            return response($output)
                ->header('Content-Type', 'application/x-'.$service.'-result')
                ->header('Cache-Control', 'no-cache, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1980 00:00:00 GMT');
        }

        return response('Internal Server Error', 500);
    }
}
