<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('user')->get();
        return view('pages.client.index', compact('clients'));
    }

    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        $user = $userId ? User::findOrFail($userId) : null;
        return view('pages.client.form', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'company_name' => 'required|string|max:255',
            'main_contact' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        Client::create($request->all());

        return redirect()->route('client.index')->with('success', 'Client profile created successfully.');
    }

    public function show(Client $client)
    {
        return response()->json([
            'client' => $client,
            'user' => $client->user
        ]);
    }

    public function edit(Client $client)
    {
        return view('pages.client.form', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'main_contact' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $client->update($request->all());

        return redirect()->route('client.index')->with('success', 'Client profile updated successfully.');
    }

    public function destroy(Client $client)
    {
        // When client is deleted, user should also be deleted (as per user request)
        $user = $client->user;
        $client->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('client.index')->with('success', 'Client and associated User account deleted successfully.');
    }
}
