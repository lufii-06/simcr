<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\User;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Http\Request;
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
        $path = $request->get('path');
        $branch = $request->get('branch', $repository->default_branch);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath.'/'.$repository->name.'.git';

        if (! file_exists($repoPath)) {
            return response()->json(['error' => 'Repository not found'], 404);
        }

        exec('cd '.escapeshellarg($repoPath).' && git show '.escapeshellarg($branch).':'.escapeshellarg($path).' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            return response()->json(['error' => 'Could not read file content'], 400);
        }

        return response()->json([
            'name' => basename($path),
            'content' => implode("\n", $output),
            'path' => $path,
            'branch' => $branch,
        ]);
    }

    public function downloadFile(Request $request, Repository $repository)
    {
        $path = $request->get('path');
        $branch = $request->get('branch', $repository->default_branch);

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

    public function show(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $repository->load(['project.owner', 'project.developers.user', 'project.developers.role']);

        $this->showAuthorize($repository, $user);

        $selectedBranch = $request->input('branch', $repository->default_branch ?? 'main');
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
            $files = $this->showGetFiles($repoPath, $selectedBranch);
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
        exec('cd '.escapeshellarg($repoPath).' && git branch', $branchesOutput);
        $branches = array_map(fn ($b) => trim(str_replace('*', '', $b)), $branchesOutput);

        if (empty($branches)) {
            $branches = [$repository->default_branch ?? 'main'];
        }

        return $branches;
    }

    private function showGetTags(string $repoPath): array
    {
        exec('cd '.escapeshellarg($repoPath).' && git tag', $tags);

        return $tags;
    }

    private function showGetRecentCommits(string $repoPath, string $selectedBranch): array
    {
        $recentCommits = [];
        exec('cd '.escapeshellarg($repoPath).' && git log '.escapeshellarg($selectedBranch).' -n 10 --pretty=format:"%h|%s|%an|%ad" --date=short 2>/dev/null', $commitsOutput);
        foreach ($commitsOutput as $line) {
            $parts = explode('|', $line);
            if (count($parts) == 4) {
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

    private function showGetFiles(string $repoPath, string $selectedBranch): array
    {
        $files = [];
        exec('cd '.escapeshellarg($repoPath).' && git ls-tree -l '.escapeshellarg($selectedBranch).' 2>/dev/null', $filesOutput);
        foreach ($filesOutput as $line) {
            if (preg_match('/^(\d+)\s+(\w+)\s+([0-9a-f]+)\s+(\d+|-)\s+(.*)$/', $line, $matches)) {
                $files[] = [
                    'type' => $matches[2],
                    'size' => $matches[4] == '-' ? '-' : round($matches[4] / 1024, 2).' KB',
                    'name' => $matches[5],
                ];
            }
        }
        usort($files, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }

            return ($a['type'] === 'tree') ? -1 : 1;
        });

        return $files;
    }

    private function showGetReadme(string $repoPath, string $selectedBranch): ?string
    {
        exec('cd '.escapeshellarg($repoPath).' && git show '.escapeshellarg($selectedBranch).':README.md 2>/dev/null', $readmeOutput);
        if (! empty($readmeOutput)) {
            return implode("\n", $readmeOutput);
        }

        return null;
    }

    private function showGetStats(string $repoPath, string $selectedBranch): array
    {
        $stats = ['size' => '0', 'files' => '0', 'objects' => '0'];

        // 1. Repo Size
        exec('du -sh '.escapeshellarg($repoPath), $sizeOutput);
        $stats['size'] = explode("\t", $sizeOutput[0] ?? '0')[0];

        // 2. Total Files Count
        exec('cd '.escapeshellarg($repoPath).' && git ls-tree -r --name-only '.escapeshellarg($selectedBranch).' | wc -l', $fileCountOutput);
        $stats['files'] = trim($fileCountOutput[0] ?? '0');

        // 3. Git Objects Count
        exec('cd '.escapeshellarg($repoPath).' && git count-objects', $objectsOutput);
        $stats['objects'] = explode(' ', $objectsOutput[0] ?? '0')[0];

        return $stats;
    }

    private function showGetContributionData(string $repoPath, $user): array
    {
        exec('cd '.escapeshellarg($repoPath).' && git shortlog -sn --all', $shortlogOutput);
        $currentUserName = $user->name;
        $currentUserCommits = 0;
        $othersCommits = 0;

        foreach ($shortlogOutput as $line) {
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
        exec('cd '.escapeshellarg($repoPath)." && git log --all --since='30 days ago' --pretty=format:\"%ad\" --date=short", $historyOutput);
        if (! empty($historyOutput)) {
            $historyCounts = array_count_values($historyOutput);
            ksort($historyCounts);

            return [
                'labels' => array_keys($historyCounts),
                'data' => array_values($historyCounts),
            ];
        }

        return ['labels' => [], 'data' => []];
    }

    private function showGetLanguages(string $repoPath, string $selectedBranch): array
    {
        $languages = [];
        exec('cd '.escapeshellarg($repoPath).' && git ls-tree -r --name-only '.escapeshellarg($selectedBranch).' 2>/dev/null', $allFiles);
        $langCounts = [];
        $totalCount = 0;
        $langMap = [
            'php' => ['name' => 'PHP', 'color' => '#4F5D95'],
            'js' => ['name' => 'JavaScript', 'color' => '#f1e05a'],
            'css' => ['name' => 'CSS', 'color' => '#563d7c'],
            'html' => ['name' => 'HTML', 'color' => '#e34c26'],
            'blade.php' => ['name' => 'Blade', 'color' => '#ff2d20'],
            'sql' => ['name' => 'SQL', 'color' => '#e38c00'],
            'py' => ['name' => 'Python', 'color' => '#3572A5'],
        ];

        foreach ($allFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (strpos($file, '.blade.php') !== false) {
                $ext = 'blade.php';
            }
            if (isset($langMap[$ext])) {
                $langCounts[$ext] = ($langCounts[$ext] ?? 0) + 1;
                $totalCount++;
            }
        }

        if ($totalCount > 0) {
            foreach ($langCounts as $ext => $count) {
                $languages[] = [
                    'name' => $langMap[$ext]['name'],
                    'color' => $langMap[$ext]['color'],
                    'percent' => round(($count / $totalCount) * 100, 1),
                ];
            }
            usort($languages, fn ($a, $b) => $b['percent'] <=> $a['percent']);
        }

        return $languages;
    }

    private function showGetDayActivityData(string $repoPath): array
    {
        exec('cd '.escapeshellarg($repoPath).' && git log --all --pretty=format:"%ad" --date=format:"%A"', $daysOutput);
        $dayCounts = [
            'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0,
            'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0,
        ];
        foreach ($daysOutput as $day) {
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
        exec('cd '.escapeshellarg($repoPath).' && git log --graph --oneline --all --decorate --color=never', $graphOutput);

        return implode("\n", $graphOutput);
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
