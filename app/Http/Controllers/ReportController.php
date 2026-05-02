<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $projects = Project::with(['client.user', 'status'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            })
            ->latest()
            ->paginate(10);

        return view('pages.report.index', compact('projects', 'search'));
    }
}
