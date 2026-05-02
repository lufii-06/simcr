<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $type = $request->get('type', 'all'); // 'all' or 'my'

        $query = Task::with(['project', 'status', 'creator', 'assignee', 'checklists']);

        if ($type === 'my') {
            $query->where('assigned_to', $user->id);
        } else {
            // All Tasks in projects user is involved in
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('user_id', $user->id) // Owner
                  ->orWhere('client_id', $user->client->id ?? 0) // Client
                  ->orWhereHas('developers', function ($sq) use ($user) { // Developer
                      $sq->where('user_id', $user->id);
                  });
            });
        }

        $tasks = $query->latest()->get();

        return view('pages.task.index', compact('tasks', 'type'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Projects user is involved in
        $projects = Project::where('user_id', $user->id)
            ->orWhereHas('developers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

        $statuses = TaskStatus::all();

        return view('pages.task.form', compact('projects', 'statuses'));
    }

    public function getProjectUsers(Project $project)
    {
        // Get all users in the project: Owner + Developers
        $owner = $project->owner;
        $developers = $project->developers()->with('user')->get()->pluck('user');
        
        $users = collect([$owner])->concat($developers)->unique('id');
        
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_status_id' => 'required|exists:task_statuses,id',
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'checklists' => 'nullable|array',
            'checklists.*' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $task = Task::create([
                'project_id' => $request->project_id,
                'task_status_id' => $request->task_status_id,
                'created_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            if ($request->has('checklists')) {
                foreach ($request->checklists as $item) {
                    TaskChecklist::create([
                        'task_id' => $task->id,
                        'item_text' => $item,
                        'is_completed' => false,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('task.index')->with('success', 'Task created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Task $task)
    {
        $task->load(['project', 'status', 'creator', 'assignee', 'checklists']);
        return response()->json($task);
    }

    public function toggleChecklist(Request $request, TaskChecklist $checklist)
    {
        // Only assigned user or creator can toggle?
        // Requirement: "developernya akan centang manual"
        if (auth()->id() !== $checklist->task->assigned_to && auth()->user()->role !== 'pm') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $checklist->update([
            'is_completed' => !$checklist->is_completed
        ]);

        $task = $checklist->task;

        return response()->json([
            'success' => true,
            'is_completed' => $checklist->is_completed,
            'progress' => $task->progress,
            'progress_text' => $task->progress_text
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['task_status_id' => 'required|exists:task_statuses,id']);
        
        $task->update(['task_status_id' => $request->task_status_id]);
        
        return response()->json(['success' => true]);
    }
}
