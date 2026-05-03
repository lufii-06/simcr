<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;

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

    public function show(Request $request, Repository $repository)
    {
        $user = auth()->user();
        $repository->load(['project.owner', 'project.developers.user', 'project.developers.role']);

        // Manual Authorization Check
        if ($user->role === 'developer') {
            $isAssigned = $repository->project->developers()->where('user_id', $user->id)->exists();
            if (!$isAssigned) abort(403, 'Unauthorized action.');
        } elseif ($user->role === 'client') {
            if ($repository->project->client_id !== ($user->client->id ?? 0)) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        $selectedBranch = $request->get('branch', $repository->default_branch ?? 'main');
        $error = null;
        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath . '/' . $repository->name . '.git';

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

        if (file_exists($repoPath)) {
            // Get Branches
            exec("cd " . escapeshellarg($repoPath) . " && git branch", $branchesOutput);
            $branches = array_map(fn($b) => trim(str_replace('*', '', $b)), $branchesOutput);

            if (empty($branches)) {
                $branches = [$repository->default_branch ?? 'main'];
            }

            // Get Tags
            exec("cd " . escapeshellarg($repoPath) . " && git tag", $tags);

            // Get Recent Commits (Last 10) for selected branch
            exec("cd " . escapeshellarg($repoPath) . " && git log " . escapeshellarg($selectedBranch) . " -n 10 --pretty=format:\"%h|%s|%an|%ad\" --date=short 2>/dev/null", $commitsOutput);
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

            // Get File List (Root level)
            exec("cd " . escapeshellarg($repoPath) . " && git ls-tree -l " . escapeshellarg($selectedBranch) . " 2>/dev/null", $filesOutput);
            foreach ($filesOutput as $line) {
                if (preg_match('/^(\d+)\s+(\w+)\s+([0-9a-f]+)\s+(\d+|-)\s+(.*)$/', $line, $matches)) {
                    $files[] = [
                        'type' => $matches[2], 
                        'size' => $matches[4] == '-' ? '-' : round($matches[4] / 1024, 2) . ' KB',
                        'name' => $matches[5],
                    ];
                }
            }
            usort($files, function ($a, $b) {
                if ($a['type'] === $b['type']) return strcasecmp($a['name'], $b['name']);
                return ($a['type'] === 'tree') ? -1 : 1;
            });

            // Get README content
            exec("cd " . escapeshellarg($repoPath) . " && git show " . escapeshellarg($selectedBranch) . ":README.md 2>/dev/null", $readmeOutput);
            if (!empty($readmeOutput)) {
                $readme = implode("\n", $readmeOutput);
            }

            // --- ADVANCED STATS ---
            // 1. Repo Size
            exec("du -sh " . escapeshellarg($repoPath), $sizeOutput);
            $stats['size'] = explode("\t", $sizeOutput[0] ?? '0')[0];

            // 2. Total Files Count
            exec("cd " . escapeshellarg($repoPath) . " && git ls-tree -r --name-only " . escapeshellarg($selectedBranch) . " | wc -l", $fileCountOutput);
            $stats['files'] = trim($fileCountOutput[0] ?? '0');

            // 3. Git Objects Count
            exec("cd " . escapeshellarg($repoPath) . " && git count-objects", $objectsOutput);
            $stats['objects'] = explode(' ', $objectsOutput[0] ?? '0')[0];

            // 4. Contribution Data
            exec("cd " . escapeshellarg($repoPath) . " && git shortlog -sn --all", $shortlogOutput);
            $currentUserName = auth()->user()->name;
            $currentUserCommits = 0;
            $othersCommits = 0;

            foreach ($shortlogOutput as $line) {
                $line = trim($line);
                if (preg_match('/(\d+)\t(.+)/', $line, $matches)) {
                    $count = (int)$matches[1];
                    $author = $matches[2];
                    if (stripos($author, $currentUserName) !== false) {
                        $currentUserCommits += $count;
                    } else {
                        $othersCommits += $count;
                    }
                }
            }
            $contributionData = [
                'labels' => ['You (' . $currentUserName . ')', 'Others'],
                'data' => [$currentUserCommits, $othersCommits]
            ];

            // 5. Activity Data
            exec("cd " . escapeshellarg($repoPath) . " && git log --all --since='30 days ago' --pretty=format:\"%ad\" --date=short", $historyOutput);
            if (!empty($historyOutput)) {
                $historyCounts = array_count_values($historyOutput);
                ksort($historyCounts);
                $activityData = [
                    'labels' => array_keys($historyCounts),
                    'data' => array_values($historyCounts)
                ];
            }

            // 6. Language Statistics
            exec("cd " . escapeshellarg($repoPath) . " && git ls-tree -r --name-only " . escapeshellarg($selectedBranch) . " 2>/dev/null", $allFiles);
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
                if (strpos($file, '.blade.php') !== false) $ext = 'blade.php';
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
                        'percent' => round(($count / $totalCount) * 100, 1)
                    ];
                }
                usort($languages, fn($a, $b) => $b['percent'] <=> $a['percent']);
            }

            // 7. Day of Week Activity
            exec("cd " . escapeshellarg($repoPath) . " && git log --all --pretty=format:\"%ad\" --date=format:\"%A\"", $daysOutput);
            $dayCounts = [
                'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 
                'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0
            ];
            foreach ($daysOutput as $day) {
                if (isset($dayCounts[$day])) $dayCounts[$day]++;
            }
            $dayActivityData = [
                'labels' => array_keys($dayCounts),
                'data' => array_values($dayCounts)
            ];

            // 8. Generate HTTP Clone URL
            $httpRoot = request()->getSchemeAndHttpHost();
            $tokenPart = !($repository->is_public) && $repository->access_token 
                ? $repository->access_token . '@' 
                : '';
            $cleanHttpRoot = str_replace(['http://', 'https://'], '', $httpRoot);
            $protocol = request()->getScheme();
            $httpUrl = "{$protocol}://{$tokenPart}{$cleanHttpRoot}/repositories/{$repository->name}.git";

            // 9. Get Git Network Graph (Visual branching)
            exec("cd " . escapeshellarg($repoPath) . " && git log --graph --oneline --all --decorate --color=never", $graphOutput);
            $gitGraph = implode("\n", $graphOutput);
        } else {
            $error = "Repository physical folder not found. Please ensure the repository has been created correctly on the server.";
            $dayActivityData = ['labels' => [], 'data' => []];
            $httpUrl = '';
            $gitGraph = '';
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

    public function toggleStatus(Repository $repository)
    {
        $newStatus = $repository->status === 'active' ? 'inactive' : 'active';
        $repository->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Repository status updated to ' . $newStatus);
    }

    public function toggleVisibility(Repository $repository)
    {
        $repository->update([
            'is_public' => !$repository->is_public
        ]);

        return back()->with('success', 'Repository visibility updated successfully.');
    }

    public function generateToken(Repository $repository)
    {
        $token = \Illuminate\Support\Str::random(40);
        $repository->update([
            'access_token' => $token
        ]);

        return back()->with('success', 'New access token generated successfully.');
    }

    public function viewFile(Request $request, Repository $repository)
    {
        $path = $request->get('path');
        $branch = $request->get('branch', $repository->default_branch);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath . '/' . $repository->name . '.git';

        if (!file_exists($repoPath)) {
            return response()->json(['error' => 'Repository not found'], 404);
        }

        exec("cd " . escapeshellarg($repoPath) . " && git show " . escapeshellarg($branch) . ":" . escapeshellarg($path) . " 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return response()->json(['error' => 'Could not read file content'], 400);
        }

        return response()->json([
            'name' => basename($path),
            'content' => implode("\n", $output),
            'path' => $path,
            'branch' => $branch
        ]);
    }

    public function downloadFile(Request $request, Repository $repository)
    {
        $path = $request->get('path');
        $branch = $request->get('branch', $repository->default_branch);

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $repoPath = $basePath . '/' . $repository->name . '.git';

        if (!file_exists($repoPath)) {
            abort(404);
        }

        $fileName = basename($path);

        return response()->streamDownload(function () use ($repoPath, $branch, $path) {
            $cmd = "cd " . escapeshellarg($repoPath) . " && git show " . escapeshellarg($branch) . ":" . escapeshellarg($path);
            $handle = popen($cmd, 'r');
            while (!feof($handle)) {
                echo fread($handle, 8192);
            }
            pclose($handle);
        }, $fileName);
    }
}
