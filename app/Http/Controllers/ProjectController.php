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
        $project->load(['client.user', 'status', 'owner', 'developers.user', 'developers.role']);
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
            $project->update($request->except('developers'));

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

        // Execute git init --bare
        exec('git init --bare '.escapeshellarg($repoPath), $output, $result);

        if ($result !== 0) {
            throw new \Exception('Gagal melakukan inisialisasi Git repository.');
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
}
