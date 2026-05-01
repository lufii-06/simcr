<?php

namespace App\Http\Controllers;

use App\Models\DeveloperStatus;
use Illuminate\Http\Request;

class DeveloperStatusController extends Controller
{
    public function index()
    {
        $statuses = DeveloperStatus::all();
        return view('pages.setting.developer-status.index', compact('statuses'));
    }

    public function create()
    {
        if (DeveloperStatus::count() >= 5) {
            return redirect()->route('developer-status.index')->with('error', 'Maximum 5 developer statuses allowed.');
        }
        return view('pages.setting.developer-status.form');
    }

    public function store(Request $request)
    {
        if (DeveloperStatus::count() >= 5) {
            return redirect()->route('developer-status.index')->with('error', 'Maximum 5 developer statuses allowed.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DeveloperStatus::create($request->all());

        return redirect()->route('developer-status.index')->with('success', 'Developer status created successfully.');
    }

    public function show(DeveloperStatus $developerStatus)
    {
        return response()->json($developerStatus);
    }

    public function edit(DeveloperStatus $developerStatus)
    {
        return view('pages.setting.developer-status.form', ['status' => $developerStatus]);
    }

    public function update(Request $request, DeveloperStatus $developerStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $developerStatus->update($request->all());

        return redirect()->route('developer-status.index')->with('success', 'Developer status updated successfully.');
    }

    public function destroy(DeveloperStatus $developerStatus)
    {
        $developerStatus->delete();
        return redirect()->route('developer-status.index')->with('success', 'Developer status deleted successfully.');
    }
}
