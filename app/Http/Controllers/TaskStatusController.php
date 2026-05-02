<?php

namespace App\Http\Controllers;

use App\Models\TaskStatus;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    public function index()
    {
        $statuses = TaskStatus::all();
        return view('pages.setting.task-status.index', compact('statuses'));
    }

    public function create()
    {
        return view('pages.setting.task-status.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        TaskStatus::create($request->all());

        return redirect()->route('task-status.index')->with('success', 'Task status created successfully.');
    }

    public function show(TaskStatus $taskStatus)
    {
        return response()->json($taskStatus);
    }

    public function edit(TaskStatus $taskStatus)
    {
        return view('pages.setting.task-status.form', ['status' => $taskStatus]);
    }

    public function update(Request $request, TaskStatus $taskStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $taskStatus->update($request->all());

        return redirect()->route('task-status.index')->with('success', 'Task status updated successfully.');
    }

    public function destroy(TaskStatus $taskStatus)
    {
        $taskStatus->delete();
        return redirect()->route('task-status.index')->with('success', 'Task status deleted successfully.');
    }
}
