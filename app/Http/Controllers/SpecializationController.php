<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specialization::all();
        return view('pages.setting.specialization.index', compact('specializations'));
    }

    public function create()
    {
        return view('pages.setting.specialization.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name',
        ]);

        Specialization::create($request->all());

        return redirect()->route('specialization.index')->with('success', 'Specialization created successfully.');
    }

    public function show(Specialization $specialization)
    {
        return response()->json($specialization);
    }

    public function edit(Specialization $specialization)
    {
        return view('pages.setting.specialization.form', compact('specialization'));
    }

    public function update(Request $request, Specialization $specialization)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name,' . $specialization->id,
        ]);

        $specialization->update($request->all());

        return redirect()->route('specialization.index')->with('success', 'Specialization updated successfully.');
    }

    public function destroy(Specialization $specialization)
    {
        // Check if being used by developers
        if ($specialization->developers()->count() > 0) {
            return redirect()->route('specialization.index')->with('error', 'Cannot delete specialization that is assigned to developers.');
        }

        $specialization->delete();
        return redirect()->route('specialization.index')->with('success', 'Specialization deleted successfully.');
    }
}
