<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use App\Models\Task;
use App\Models\Repository;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function master(Request $request)
    {
        $category = $request->get('category', 'client');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $specializationId = $request->get('specialization_id');

        $specializations = \App\Models\Specialization::all();

        if ($category == 'client') {
            $query = Client::with('user');
        } else {
            $query = Developer::with(['user', 'specialization']);
            if ($specializationId) {
                $query->where('specialization_id', $specializationId);
            }
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $data = $query->latest()->get();

        return view('pages.report.master', compact('data', 'category', 'startDate', 'endDate', 'specializations', 'specializationId'));
    }

    public function project(Request $request)
    {
        $clientId = $request->get('client_id');
        $statusId = $request->get('status_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $clients = Client::all();
        $statuses = \App\Models\ProjectStatus::all();

        $query = Project::with(['client', 'status']);

        if ($clientId) $query->where('client_id', $clientId);
        if ($statusId) $query->where('project_status_id', $statusId);
        if ($startDate) $query->whereDate('start_date', '>=', $startDate);
        if ($endDate) $query->whereDate('end_date', '<=', $endDate);

        $data = $query->latest()->get();

        return view('pages.report.project', compact('data', 'clients', 'statuses', 'clientId', 'statusId', 'startDate', 'endDate'));
    }

    public function task(Request $request)
    {
        $projectId = $request->get('project_id');
        $assigneeId = $request->get('assigned_to');
        $statusId = $request->get('status_id');

        $projects = Project::all();
        $developers = Developer::with('user')->get();
        $statuses = \App\Models\TaskStatus::all();

        $query = Task::with(['project', 'assignee', 'status']);

        if ($projectId) $query->where('project_id', $projectId);
        if ($assigneeId) $query->where('assigned_to', $assigneeId);
        if ($statusId) $query->where('task_status_id', $statusId);

        $data = $query->latest()->get();

        return view('pages.report.task', compact('data', 'projects', 'developers', 'statuses', 'projectId', 'assigneeId', 'statusId'));
    }

    public function repository(Request $request)
    {
        $projectId = $request->get('project_id');
        $visibility = $request->get('visibility');
        $status = $request->get('status');

        $projects = Project::all();

        $query = Repository::with('project');

        if ($projectId) $query->where('project_id', $projectId);
        if ($visibility) $query->where('is_public', $visibility == 'public');
        if ($status) $query->where('is_active', $status == 'active');

        $data = $query->latest()->get();

        return view('pages.report.repository', compact('data', 'projects', 'projectId', 'visibility', 'status'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->get('type');
        $category = $request->get('category');
        
        $data = $this->getFilteredData($request, $type, $category);
        
        // Additional info for header
        $specName = null;
        $clientName = null;
        $statusName = null;
        $projectName = null;
        $assigneeName = null;

        if ($type == 'master' && $request->specialization_id) {
            $specName = \App\Models\Specialization::find($request->specialization_id)?->name;
        } elseif ($type == 'project') {
            if ($request->client_id) $clientName = Client::find($request->client_id)?->company_name;
            if ($request->status_id) $statusName = \App\Models\ProjectStatus::find($request->status_id)?->name;
        } elseif ($type == 'task') {
            if ($request->project_id) $projectName = Project::find($request->project_id)?->name;
            if ($request->assigned_to) $assigneeName = User::find($request->assigned_to)?->name;
            if ($request->status_id) $statusName = \App\Models\TaskStatus::find($request->status_id)?->name;
        } elseif ($type == 'repository') {
            if ($request->project_id) $projectName = Project::find($request->project_id)?->name;
        }

        $viewPath = $type == 'master' ? "pages.report.exports.master_{$category}_pdf" : "pages.report.exports.{$type}_pdf";

        $pdf = Pdf::loadView($viewPath, [
            'data' => $data,
            'category' => $category,
            'filters' => $request->all(),
            'specialization_name' => $specName,
            'client_name' => $clientName,
            'status_name' => $statusName,
            'project_name' => $projectName,
            'assignee_name' => $assigneeName,
            'title' => "LAPORAN " . strtoupper($type)
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan_{$type}_" . date('YmdHis') . ".pdf");
    }

    public function exportExcel(Request $request)
    {
        $type = $request->get('type');
        $category = $request->get('category');
        
        if ($type == 'master') {
            $exportClass = $category == 'client' ? new \App\Exports\ClientExport($request->all()) : new \App\Exports\DeveloperExport($request->all());
        } elseif ($type == 'project') {
            $exportClass = new \App\Exports\ProjectExport($request->all());
        } elseif ($type == 'task') {
            $exportClass = new \App\Exports\TaskExport($request->all());
        } elseif ($type == 'repository') {
            $exportClass = new \App\Exports\RepositoryExport($request->all());
        } else {
            return back()->with('error', 'Export format not supported yet.');
        }
        
        return Excel::download($exportClass, "laporan_{$type}_" . date('YmdHis') . ".xlsx");
    }

    private function getFilteredData($request, $type, $category = null)
    {
        if ($type == 'master') {
            if ($category == 'client') {
                $query = Client::with('user');
            } else {
                $query = Developer::with(['user', 'specialization']);
                if ($request->specialization_id) $query->where('specialization_id', $request->specialization_id);
            }
            if ($request->start_date) $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->end_date) $query->whereDate('created_at', '<=', $request->end_date);
            return $query->latest()->get();
        }

        if ($type == 'project') {
            $query = Project::with(['client', 'status']);
            if ($request->client_id) $query->where('client_id', $request->client_id);
            if ($request->status_id) $query->where('project_status_id', $request->status_id);
            if ($request->start_date) $query->whereDate('start_date', '>=', $request->start_date);
            if ($request->end_date) $query->whereDate('end_date', '<=', $request->end_date);
            return $query->latest()->get();
        }

        if ($type == 'task') {
            $query = Task::with(['project', 'assignee', 'status']);
            if ($request->project_id) $query->where('project_id', $request->project_id);
            if ($request->assigned_to) $query->where('assigned_to', $request->assigned_to);
            if ($request->status_id) $query->where('task_status_id', $request->status_id);
            return $query->latest()->get();
        }

        if ($type == 'repository') {
            $query = Repository::with('project');
            if ($request->project_id) $query->where('project_id', $request->project_id);
            if ($request->visibility) $query->where('is_public', $request->visibility == 'public');
            if ($request->status) $query->where('is_active', $request->status == 'active');
            return $query->latest()->get();
        }

        return collect([]);
    }
}
