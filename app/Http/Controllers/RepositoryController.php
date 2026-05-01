<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function index()
    {
        $repositories = Repository::with('project')->orderBy('created_at', 'desc')->get();
        return view('pages.repository.index', compact('repositories'));
    }

    public function show($id)
    {
        $repository = Repository::with('project')->findOrFail($id);
        return view('pages.repository.show', compact('repository'));
    }
}
