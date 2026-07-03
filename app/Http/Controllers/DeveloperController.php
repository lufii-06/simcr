<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function index()
    {
        $developers = Developer::with(['user', 'specialization'])->get();

        return view('pages.developer.index', compact('developers'));
    }

    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        $user = $userId ? User::findOrFail($userId) : null;
        $specializations = Specialization::all();

        return view('pages.developer.form', compact('user', 'specializations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'specialization_id' => 'required|exists:specializations,id',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        Developer::create($request->all());

        return redirect()->route('developer.index')->with('success', 'Developer profile created successfully.');
    }

    public function show(Developer $developer)
    {
        return response()->json([
            'developer' => $developer,
            'user' => $developer->user,
            'specialization' => $developer->specialization,
        ]);
    }

    public function edit(Developer $developer)
    {
        $specializations = Specialization::all();

        return view('pages.developer.form', compact('developer', 'specializations'));
    }

    public function update(Request $request, Developer $developer)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'specialization_id' => 'required|exists:specializations,id',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        $developer->update($request->all());

        return redirect()->route('developer.index')->with('success', 'Developer profile updated successfully.');
    }

    public function destroy(Developer $developer)
    {
        $user = $developer->user;
        $developer->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('developer.index')->with('success', 'Developer and associated User account deleted successfully.');
    }

    public function performance(Developer $developer)
    {
        $developer->load(['user', 'specialization']);
        $user = $developer->user;

        if (!$user) {
            return back()->with('error', 'Developer does not have an associated user account.');
        }

        $basePath = base_path(env('REPO_BASE_PATH', '../repositories'));
        $doneStatusName = 'Done';

        // Get all projects this developer is assigned to
        $projectDeveloperEntries = \App\Models\ProjectDeveloper::with(['project.repository', 'role'])
            ->where('user_id', $user->id)
            ->get()
            ->unique('project_id'); // deduplicate if multiple roles in same project

        $projectStats = [];
        $totalTasksAll = 0;
        $totalDoneAll = 0;
        $totalCommitsAll = 0;

        foreach ($projectDeveloperEntries as $entry) {
            $project = $entry->project;
            if (!$project) continue;

            // --- Task stats ---
            $tasks = \App\Models\Task::with('status')
                ->where('project_id', $project->id)
                ->where('assigned_to', $user->id)
                ->get();

            $totalTasks = $tasks->count();
            $doneTasks  = $tasks->filter(fn($t) => strtolower($t->status->name ?? '') === strtolower($doneStatusName))->count();
            $pendingTasks = $totalTasks - $doneTasks;
            $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

            // --- Git commit stats ---
            $commitCount = 0;
            $repoPath = null;
            if ($project->repository) {
                $repoPath = $basePath . '/' . $project->repository->name . '.git';
                if (file_exists($repoPath)) {
                    $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
                    $cmd = 'git --git-dir=' . escapeshellarg($repoPath)
                        . ' shortlog -sn --all 2>' . $nullDevice;
                    exec($cmd, $gitOutput);

                    foreach ($gitOutput as $line) {
                        $line = trim($line);
                        if (preg_match('/^(\d+)\t(.+)$/', $line, $m)) {
                            if (stripos($m[2], $user->name) !== false) {
                                $commitCount = (int) $m[1];
                                break;
                            }
                        }
                    }
                    unset($gitOutput);
                }
            }

            $totalTasksAll   += $totalTasks;
            $totalDoneAll    += $doneTasks;
            $totalCommitsAll += $commitCount;

            // Build per-project task list for the "By Task" tab detail
            $taskList = $tasks->map(function ($t) use ($doneStatusName) {
                return [
                    'code'    => $t->code,
                    'title'   => $t->title,
                    'status'  => $t->status->name ?? '-',
                    'is_done' => strtolower($t->status->name ?? '') === strtolower($doneStatusName),
                ];
            })->values()->toArray();

            $projectStats[] = [
                'project'         => $project,
                'role'            => $entry->role->name ?? '-',
                'total_tasks'     => $totalTasks,
                'done_tasks'      => $doneTasks,
                'pending_tasks'   => $pendingTasks,
                'completion_rate' => $completionRate,
                'commit_count'    => $commitCount,
                'has_repo'        => $project->repository !== null,
                'task_list'       => $taskList,
            ];
        }

        $globalCompletionRate = $totalTasksAll > 0
            ? round(($totalDoneAll / $totalTasksAll) * 100)
            : 0;

        return view('pages.developer.performance', compact(
            'developer',
            'user',
            'projectStats',
            'totalTasksAll',
            'totalDoneAll',
            'totalCommitsAll',
            'globalCompletionRate'
        ));
    }

    public function toggleRole(Developer $developer)
    {
        $user = $developer->user;
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Associated user account not found.'
            ], 404);
        }

        // Toggle the role between 'developer' and 'pm'
        $newRole = $user->role === 'developer' ? 'pm' : 'developer';
        $user->role = $newRole;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Successfully changed role of {$user->name} to " . strtoupper($newRole) . ".",
            'new_role' => $newRole
        ]);
    }
}
