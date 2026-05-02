<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Stats Data
        $projectQuery = Project::query();
        if ($user->role === 'developer') {
            $projectQuery->whereHas('developers', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === 'client') {
            $projectQuery->where('client_id', $user->client->id ?? 0);
        }
        $totalProjects = $projectQuery->count();

        $totalClients = ($user->role === 'pm') ? Client::count() : 0;

        $taskQuery = Task::query();
        if ($user->role === 'developer') {
            $taskQuery->where('assigned_to', $user->id);
        } elseif ($user->role === 'client' || $user->role === 'pm' || $user->role === 'leader') {
            $taskQuery->whereHas('project', function($q) use ($user) {
                if ($user->role === 'client') {
                    $q->where('client_id', $user->client->id ?? 0);
                } elseif ($user->role != 'pm') {
                    $q->where('user_id', $user->id)->orWhereHas('developers', fn($sq) => $sq->where('user_id', $user->id));
                }
            });
        }
        
        $completedTasks = (clone $taskQuery)->whereHas('status', fn($q) => $q->where('name', 'Done'))->count();
        $pendingTasks = (clone $taskQuery)->whereHas('status', fn($q) => $q->where('name', '!=', 'Done'))->count();
        $tasksToday = (clone $taskQuery)->whereDate('created_at', now()->today())->count();

        // 2. Recent Tasks
        $recentTasks = $taskQuery->with(['project', 'status', 'assignee'])->latest()->take(6)->get();

        // 3. Chart Data: Tasks by Status
        $statusChart = TaskStatus::withCount(['tasks' => function($q) use ($user) {
            if ($user->role === 'developer') {
                $q->where('assigned_to', $user->id);
            }
        }])->get();

        return view('pages.dashboard-overview', compact(
            'totalProjects', 'totalClients', 'completedTasks', 'pendingTasks', 
            'tasksToday', 'recentTasks', 'statusChart'
        ));
    }
}
