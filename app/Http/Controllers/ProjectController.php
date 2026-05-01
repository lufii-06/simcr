<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeveloperStatus;
use App\Models\Project;
use App\Models\ProjectDeveloper;
use App\Models\ProjectStatus;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['client.user', 'status', 'owner'])->get();
        return view('pages.project.index', compact('projects'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        if (!$query) {
            return response()->json([]);
        }
        $projects = Project::where('name', 'like', "%{$query}%")->limit(5)->get()->map(function($p) {
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
        $users = User::all();
        return view('pages.project.form', compact('clients', 'projectStatuses', 'developerStatuses', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_status_id' => 'required|exists:project_statuses,id',
            'user_id' => 'required|exists:users,id',
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
            $project = Project::create($request->except('developers'));

            // Auto-create blank repository for this project
            Repository::create([
                'project_id' => $project->id,
                'name' => Str::slug($project->name),
                'default_branch' => 'main',
                'status' => 'active',
            ]);

            if ($request->has('developers')) {
                foreach ($request->developers as $developer) {
                    ProjectDeveloper::create([
                        'project_id' => $project->id,
                        'user_id' => $developer['user_id'],
                        'developer_status_id' => $developer['developer_status_id']
                    ]);

                    $userToNotify = User::find($developer['user_id']);
                    $roleForNotify = DeveloperStatus::find($developer['developer_status_id']);
                    if ($userToNotify && $roleForNotify && $userToNotify->id !== Auth::id()) {
                        $userToNotify->notify(new \App\Notifications\ProjectAssignedNotification($project, $roleForNotify->name));
                    }
                }
            }
            DB::commit();
            return redirect()->route('project.index')->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating project: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $project = Project::with(['client.user', 'status', 'owner', 'developers.user', 'developers.role'])->findOrFail($id);
        return response()->json($project);
    }

    public function edit(Project $project)
    {
        $clients = Client::with('user')->get();
        $projectStatuses = ProjectStatus::all();
        $developerStatuses = DeveloperStatus::all();
        $users = User::all();
        $project->load('developers');
        
        return view('pages.project.form', compact('project', 'clients', 'projectStatuses', 'developerStatuses', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_status_id' => 'required|exists:project_statuses,id',
            'user_id' => 'required|exists:users,id',
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
            $project->update($request->except('developers'));

            $project->developers()->delete(); // remove existing
            if ($request->has('developers')) {
                foreach ($request->developers as $developer) {
                    ProjectDeveloper::create([
                        'project_id' => $project->id,
                        'user_id' => $developer['user_id'],
                        'developer_status_id' => $developer['developer_status_id']
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
            return back()->with('error', 'Error updating project: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('project.index')->with('success', 'Project deleted successfully.');
    }
}
