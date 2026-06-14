<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeveloperStatus;
use App\Models\Project;
use App\Models\ProjectDeveloper;
use App\Models\ProjectStatus;
use App\Models\Repository;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Project::with(['client.user', 'status', 'owner']);

        if ($user->role === 'developer') {
            $query->whereHas('developers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'client') {
            $query->where('client_id', $user->client->id ?? 0);
        }

        $projects = $query->latest()->get();

        if ($request->get('view') == 'analytics') {
            return view('pages.project.analytics_list', compact('projects'));
        }

        return view('pages.project.index', compact('projects'));
    }

    public function search(Request $request)
    {
        $queryText = $request->get('q');
        if (! $queryText) {
            return response()->json([]);
        }

        $user = auth()->user();
        $query = Project::where('name', 'like', "%{$queryText}%");

        if ($user->role === 'developer') {
            $query->whereHas('developers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'client') {
            $query->where('client_id', $user->client->id ?? 0);
        }

        $projects = $query->limit(5)->get()->map(function ($p) {
            $p->encrypted_id = $p->getRouteKey();

            return $p;
        });

        return response()->json($projects);
    }

    public function create()
    {
        $clients = Client::with('user')->get();
        $projectStatuses = ProjectStatus::all();
        $developerStatuses = DeveloperStatus::all();
        $users = User::where('id', '!=', auth()->id())->get();

        return view('pages.project.form', compact('clients', 'projectStatuses', 'developerStatuses', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_status_id' => 'required|exists:project_statuses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'developers' => 'nullable|array',
            'developers.*.user_id' => 'required|exists:users,id',
            'developers.*.developer_status_id' => 'required|exists:developer_statuses,id',
        ]);

        try {
            DB::beginTransaction();
            $data = $request->except('developers');
            $data['user_id'] = auth()->id();
            $project = Project::create($data);

            // Notify client if client is set and has an associated user
            if ($project->client && $project->client->user) {
                $clientUser = $project->client->user;
                if ($clientUser->id !== auth()->id()) {
                    $clientUser->notify(new \App\Notifications\ProjectAssignedNotification($project, 'Client'));
                }
            }

            if ($request->has('developers')) {
                foreach ($request->developers as $developer) {
                    ProjectDeveloper::create([
                        'project_id' => $project->id,
                        'user_id' => $developer['user_id'],
                        'developer_status_id' => $developer['developer_status_id'],
                    ]);

                    $userToNotify = User::find($developer['user_id']);
                    $roleForNotify = DeveloperStatus::find($developer['developer_status_id']);
                    if ($userToNotify && $roleForNotify && $userToNotify->id !== Auth::id()) {
                        $userToNotify->notify(new \App\Notifications\ProjectAssignedNotification($project, $roleForNotify->name));
                    }
                }
            }
            $this->createBlankRepository($project);
            DB::commit();

            return redirect()->route('project.index')->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error creating project: '.$e->getMessage())->withInput();
        }
    }

    public function show(Project $project)
    {
        $project->load(['client.user', 'status', 'owner', 'developers.user', 'developers.role', 'repository']);
        return response()->json($project);
    }

    public function edit(Project $project)
    {
        $clients = Client::with('user')->get();
        $projectStatuses = ProjectStatus::all();
        $developerStatuses = DeveloperStatus::all();
        $users = User::where('id', '!=', auth()->id())->get();
        $project->load('developers');

        return view('pages.project.form', compact('project', 'clients', 'projectStatuses', 'developerStatuses', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_status_id' => 'required|exists:project_statuses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'developers' => 'nullable|array',
            'developers.*.user_id' => 'required|exists:users,id',
            'developers.*.developer_status_id' => 'required|exists:developer_statuses,id',
        ]);

        try {
            DB::beginTransaction();
            $oldName = $project->name;
            $oldClientId = $project->client_id;
            $project->update($request->except('developers'));

            // Notify client if client changed or was updated
            if ($project->client && $project->client->user) {
                $clientUser = $project->client->user;
                if ($oldClientId !== $project->client_id && $clientUser->id !== auth()->id()) {
                    $clientUser->notify(new \App\Notifications\ProjectAssignedNotification($project, 'Client'));
                }
            }

            if ($oldName !== $request->name) {
                $this->renameRepository($project, $request->name);
            }

            $project->developers()->delete(); // remove existing
            if ($request->has('developers')) {
                foreach ($request->developers as $developer) {
                    ProjectDeveloper::create([
                        'project_id' => $project->id,
                        'user_id' => $developer['user_id'],
                        'developer_status_id' => $developer['developer_status_id'],
                    ]);

                    $userToNotify = User::find($developer['user_id']);
                    $roleForNotify = DeveloperStatus::find($developer['developer_status_id']);
                    if ($userToNotify && $roleForNotify && $userToNotify->id !== Auth::id()) {
                        $userToNotify->notify(new \App\Notifications\ProjectAssignedNotification($project, $roleForNotify->name));
                    }
                }
            }
            DB::commit();

            return redirect()->route('project.index')->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error updating project: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Project $project)
    {
        try {
            DB::beginTransaction();

            $this->deleteRepository($project);

            $project->delete();

            DB::commit();

            return redirect()->route('project.index')->with('success', 'Project deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error deleting project: '.$e->getMessage());
        }
    }

    /**
     * Get the base path for all repositories.
     * Ensure the directory exists.
     */
    private function getRepoBasePath(): string
    {
        $path = base_path(env('REPO_BASE_PATH', '../repositories'));
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $path;
    }

    /**
     * Generate a unique repository folder name.
     */
    private function generateRepoName(Project $project, ?string $customName = null): string
    {
        $name = $customName ?: $project->name;
        $prefix = $project->code ?? ('PRJ-'.str_pad($project->id, 4, '0', STR_PAD_LEFT));

        return $prefix.'-'.Str::slug($name);
    }

    private function createBlankRepository(Project $project): void
    {
        $repoName = $this->generateRepoName($project);
        $basePath = $this->getRepoBasePath();
        $repoPath = $basePath.'/'.$repoName.'.git';

        // Check if physical folder already exists
        if (File::exists($repoPath)) {
            throw new \Exception("Folder repository '{$repoName}' sudah ada di server.");
        }

        // Create a temporary non-bare repository in Laravel storage
        $tempPath = storage_path('app/temp_git_'.uniqid());
        try {
            File::makeDirectory($tempPath, 0755, true);

            // Initialize standard repository
            exec('git -C '.escapeshellarg($tempPath).' init', $output, $result);
            if ($result !== 0) {
                throw new \Exception('Gagal inisialisasi git di folder temporary.');
            }

            // Create initial README.md file
            $readmeContent = "# {$project->name}\n\nInitial repository setup for **{$project->name}** under SIMCR.";
            File::put($tempPath.'/README.md', $readmeContent);

            // Configure local user credentials for the commit
            exec('git -C '.escapeshellarg($tempPath).' config user.name "SIMCR System"');
            exec('git -C '.escapeshellarg($tempPath).' config user.email "system@simcr.com"');

            // Stage and commit the file
            exec('git -C '.escapeshellarg($tempPath).' add README.md');
            exec('git -C '.escapeshellarg($tempPath).' commit -m "Initial commit by SIMCR"');

            // Rename the default branch to 'main' (ensuring compatibility across all git versions)
            exec('git -C '.escapeshellarg($tempPath).' branch -m main');

            // Convert to bare repository at the target path
            exec('git clone --bare '.escapeshellarg($tempPath).' '.escapeshellarg($repoPath), $output, $result);
            if ($result !== 0 || !File::exists($repoPath)) {
                throw new \Exception('Gagal melakukan cloning ke bare repository.');
            }
        } finally {
            // Clean up temporary directory
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
        }

        // Save to database
        $rootUrl = env('REPO_ROOT_URL', 'git@localhost');
        $fullUrl = $rootUrl.':repositories/'.$repoName.'.git';

        Repository::create([
            'project_id' => $project->id,
            'name' => $repoName,
            'url' => $fullUrl,
            'default_branch' => 'main',
            'status' => 'active',
        ]);
    }

    private function renameRepository(Project $project, string $newName): void
    {
        $repository = $project->repository;
        if (! $repository) {
            return;
        }

        $basePath = $this->getRepoBasePath();
        $newRepoName = $this->generateRepoName($project, $newName);

        $oldPath = $basePath.'/'.$repository->name.'.git';
        $newPath = $basePath.'/'.$newRepoName.'.git';

        // Skip if name is exactly the same
        if ($repository->name === $newRepoName) {
            return;
        }

        // Safety check: Don't overwrite existing folder
        if (File::exists($newPath)) {
            throw new \Exception("Gagal mengubah nama: Folder '{$newRepoName}' sudah digunakan.");
        }

        // Perform physical rename if old folder exists
        if (File::exists($oldPath)) {
            File::move($oldPath, $newPath);
        }

        // Sync with database
        $rootUrl = env('REPO_ROOT_URL', 'git@localhost');
        $fullUrl = $rootUrl.':repositories/'.$newRepoName.'.git';

        $repository->update([
            'name' => $newRepoName,
            'url' => $fullUrl,
        ]);
    }

    private function deleteRepository(Project $project): void
    {
        $repository = $project->repository;
        if (! $repository) {
            return;
        }

        $basePath = $this->getRepoBasePath();
        $repoPath = $basePath.'/'.$repository->name.'.git';

        // Delete physical directory
        if (File::exists($repoPath)) {
            File::deleteDirectory($repoPath);
        }

        // Delete database record
        $repository->delete();
    }

    public function analytics(Project $project)
    {
        $project->load(['developers.user', 'developers.role', 'owner', 'tasks.status', 'tasks.checklists']);

        $teamStats = $project->developers->map(function ($dev) use ($project) {
            $userTasks = $project->tasks->where('assigned_to', $dev->user_id);
            $totalTasks = $userTasks->count();
            $completedTasks = $userTasks->filter(fn ($t) => $t->status && strtolower($t->status->name) === 'done')->count();

            $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

            // Get logs for this user in this project
            $logs = TaskLog::where('user_id', $dev->user_id)
                ->whereHas('task', function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                })
                ->latest()
                ->take(10)
                ->get();

            return [
                'user' => $dev->user,
                'role' => $dev->role,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'progress' => $progress,
                'logs' => $logs,
            ];
        });

        $projectLogs = \App\Models\TaskLog::whereHas('task', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })
            ->with(['user', 'task.status'])
            ->latest()
            ->take(20)
            ->get();

        return view('pages.project.analytics', compact('project', 'teamStats', 'projectLogs'));
    }

    public function exportProjectPdf(Project $project)
    {
        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $doneStatusName = 'Done';

        $project->load([
            'client.user',
            'status',
            'owner',
            'developers.user',
            'developers.role',
            'repository',
            'tasks.status',
            'tasks.assignee',
            'tasks.checklists',
        ]);

        // Build per-developer task stats & commit count
        $developerStats = [];

        // Unique user entries in this project
        $assignedUsers = $project->developers
            ->map(fn($d) => $d->user)
            ->filter()
            ->unique('id');

        foreach ($assignedUsers as $devUser) {
            $userTasks = $project->tasks->where('assigned_to', $devUser->id);
            $totalTasks  = $userTasks->count();
            $doneTasks   = $userTasks->filter(fn($t) => strtolower($t->status->name ?? '') === strtolower($doneStatusName))->count();
            $pendingTasks = $totalTasks - $doneTasks;
            $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

            // Git commits
            $commitCount = 0;
            if ($project->repository) {
                $repoPath = $basePath . '/' . $project->repository->name . '.git';
                if (file_exists($repoPath)) {
                    $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
                    exec('git --git-dir=' . escapeshellarg($repoPath) . ' shortlog -sn --all 2>' . $nullDevice, $gitOutput);
                    foreach ($gitOutput as $line) {
                        $line = trim($line);
                        if (preg_match('/^(\d+)\t(.+)$/', $line, $m)) {
                            if (stripos($m[2], $devUser->name) !== false) {
                                $commitCount = (int) $m[1];
                                break;
                            }
                        }
                    }
                    unset($gitOutput);
                }
            }

            // Get role in this project
            $roleEntry = $project->developers->firstWhere('user_id', $devUser->id);
            $developerStats[] = [
                'name'            => $devUser->name,
                'role'            => $roleEntry?->role?->name ?? '-',
                'total_tasks'     => $totalTasks,
                'done_tasks'      => $doneTasks,
                'pending_tasks'   => $pendingTasks,
                'completion_rate' => $completionRate,
                'commit_count'    => $commitCount,
            ];
        }

        // Summary task totals
        $totalTasks  = $project->tasks->count();
        $doneTasks   = $project->tasks->filter(fn($t) => strtolower($t->status->name ?? '') === strtolower($doneStatusName))->count();
        $pendingTasks = $totalTasks - $doneTasks;
        $globalRate  = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
        $generatedAt = now()->format('d M Y H:i');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.project.pdf_report', compact(
            'project',
            'developerStats',
            'totalTasks',
            'doneTasks',
            'pendingTasks',
            'globalRate',
            'generatedAt'
        ))->setPaper('a4', 'portrait');

        $filename = 'Project_Report_' . \Illuminate\Support\Str::slug($project->name) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
