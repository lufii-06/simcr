<?php

namespace App\Http\Controllers;

use App\Models\ProjectStatus;
use Illuminate\Http\Request;

class ProjectStatusController extends Controller
{
    public function index()
    {
        $statuses = ProjectStatus::all();
        return view('pages.setting.project-status.index', compact('statuses'));
    }

    public function create()
    {
        if (ProjectStatus::count() >= 5) {
            return redirect()->route('project-status.index')->with('error', 'Maximum 5 project statuses allowed.');
        }
        return view('pages.setting.project-status.form');
    }

    public function store(Request $request)
    {
        if (ProjectStatus::count() >= 5) {
            return redirect()->route('project-status.index')->with('error', 'Maximum 5 project statuses allowed.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ProjectStatus::create($request->all());

        return redirect()->route('project-status.index')->with('success', 'Project status created successfully.');
    }

    public function show(ProjectStatus $projectStatus)
    {
        return response()->json($projectStatus);
    }

    public function edit(ProjectStatus $projectStatus)
    {
        return view('pages.setting.project-status.form', ['status' => $projectStatus]);
    }

    public function update(Request $request, ProjectStatus $projectStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $projectStatus->update($request->all());

        return redirect()->route('project-status.index')->with('success', 'Project status updated successfully.');
    }

    public function destroy(ProjectStatus $projectStatus)
    {
        $projectStatus->delete();
        return redirect()->route('project-status.index')->with('success', 'Project status deleted successfully.');
    }
}
