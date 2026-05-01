<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\User;
use App\Models\Specialization;
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
            'specialization' => $developer->specialization
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
}
