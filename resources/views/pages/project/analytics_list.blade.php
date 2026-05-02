@extends('dashboard')

@section('title', 'Project Analytics - Select Project')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Select Project for Analytics</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="analytics-project-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th style="width: 15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->client->user->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-info">{{ $project->status->name ?? 'N/A' }}</span></td>
                                        <td>
                                            <a href="{{ route('project.analytics', $project) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-chart-line"></i> View Analytics
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No projects available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#analytics-project-datatables').DataTable({});
        });
    </script>
@endpush
