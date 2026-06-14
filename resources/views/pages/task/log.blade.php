@extends('dashboard')

@section('title', 'Task Activity Log')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Task Activity History</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form action="{{ route('task.log') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filter Project</label>
                                    <select name="project_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Projects</option>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filter User</label>
                                    <select name="user_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Users</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Action Type</label>
                                    <select name="action" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Actions</option>
                                        <option value="created" {{ $action == 'created' ? 'selected' : '' }}>Task Created</option>
                                        <option value="status_changed" {{ $action == 'status_changed' ? 'selected' : '' }}>Status Changed</option>
                                        <option value="checklist_toggled" {{ $action == 'checklist_toggled' ? 'selected' : '' }}>Checklist Toggled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <a href="{{ route('task.log') }}" class="btn btn-secondary">Reset Filter</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover mt-3">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Project</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $log->task?->project?->name ?? 'N/A' }}</td>
                                        <td><strong>{{ $log->user?->name ?? 'N/A' }}</strong></td>
                                        <td>
                                            @if($log->action == 'created')
                                                <span class="badge badge-primary">Created</span>
                                            @elseif($log->action == 'status_changed')
                                                <span class="badge badge-warning">Status Changed</span>
                                            @elseif($log->action == 'checklist_toggled')
                                                <span class="badge badge-success">Checklist</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->details }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $logs->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
