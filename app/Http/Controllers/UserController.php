<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('pages.user.index', compact('users'));
    }

    public function create()
    {
        return view('pages.user.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['client', 'pm', 'developer', 'leader'])],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars');
            $userData['avatar'] = basename($path);
        }

        $user = User::create($userData);

        if ($request->role == 'client') {
            return redirect()->route('client.create', ['user_id' => $user->id])->with('success', 'User created. Please complete the client profile.');
        }

        if ($request->role == 'developer') {
            return redirect()->route('developer.create', ['user_id' => $user->id])->with('success', 'User created. Please complete the developer profile.');
        }

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return response()->json([
            'user' => $user,
            'avatar_url' => $user->getAvatarUrl()
        ]);
    }

    public function edit(User $user)
    {
        return view('pages.user.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['client', 'pm', 'developer', 'leader'])],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);


        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::delete('avatars/' . $user->avatar);
            }
            $path = $request->file('avatar')->store('avatars');
            $userData['avatar'] = basename($path);
        }

        $user->update($userData);

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->avatar) {
            Storage::delete('avatars/' . $user->avatar);
        }

        if ($user->role == 'client' && $user->client) {
            $user->client->delete();
        }
        if ($user->role == 'developer' && $user->developer) {
            $user->developer->delete();
        }

        $user->delete();
        return redirect()->route('user.index')->with('success', 'User deleted successfully.');
    }

    public function serveAvatar($filename)
    {
        $path = 'avatars/' . $filename;
        if (!Storage::exists($path)) {
            abort(404);
        }

        $file = Storage::get($path);
        $type = Storage::mimeType($path);

        return response($file)->header('Content-Type', $type);
    }

    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return redirect()->route('user.index')
            ->with('success', "Password for {$user->name} has been reset successfully.")
            ->with('new_password', $newPassword)
            ->with('reset_user', $user->name);
    }
}
