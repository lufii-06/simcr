<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load(['client', 'developer.specialization']);
        $specializations = \App\Models\Specialization::all();

        return view('pages.profile', compact('user', 'specializations'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];

        // Role-based validation
        if ($user->role === 'client') {
            $rules += [
                'company_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
            ];
        } elseif ($user->role === 'developer') {
            $rules += [
                'specialization_id' => 'required|exists:specializations,id',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'portfolio_url' => 'nullable|url|max:255',
            ];
        }
        $request->validate($rules);

        $userData = $request->only('name', 'email');

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::delete('avatars/' . $user->avatar);
            }

            $filename = time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('avatars', $filename);
            $userData['avatar'] = $filename;
        }
        $user->update($userData);

        // Update role-specific profile
        if ($user->role === 'client') {
            $user->client()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only('company_name', 'phone', 'address')
            );
        } elseif ($user->role === 'developer') {
            $user->developer()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only('specialization_id', 'phone', 'address', 'portfolio_url')
            );
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
