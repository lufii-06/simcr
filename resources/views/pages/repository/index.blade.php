@extends('dashboard')

@section('title', 'Git Repositories')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">List of Repositories</h4>
                        <!-- No Add button here because it's created via Project -->
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="repo-status-filter">Filter by Status:</label>
                            <select id="repo-status-filter" class="form-control">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="repository-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Repository Name</th>
                                    <th>Project</th>
                                    <th>Default Branch</th>
                                    <th>Status</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($repositories as $repo)
                                    <tr>
                                        <td><strong>{{ $repo->name ?? 'Unknown' }}</strong></td>
                                        <td>{{ $repo->project?->name ?? 'No Project' }}</td>
                                        <td><span class="badge badge-secondary">{{ $repo->default_branch ?? 'main' }}</span></td>
                                        <td>
                                            @if (($repo->status ?? '') === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">{{ ucfirst($repo->status ?? 'inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <a href="{{ route('repository.show', $repo) }}"
                                                    class="btn btn-link btn-info btn-lg" data-bs-toggle="tooltip"
                                                    title="View Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No repositories found.</td>
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
            var table = $('#repository-datatables').DataTable({});

            $('#repo-status-filter').on('change', function() {
                var val = $(this).val();
                table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });
        });
    </script>
@endpush
