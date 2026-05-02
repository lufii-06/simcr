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
                    <div class="table-responsive">
                        <table id="repository-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Repository Name</th>
                                    <th>Project</th>
                                    <th>Default Branch</th>
                                    <th>Status</th>
                                    <th>Clone URL</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($repositories as $repo)
                                    <tr>
                                        <td><strong>{{ $repo->name }}</strong></td>
                                        <td>{{ $repo->project->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-secondary">{{ $repo->default_branch }}</span></td>
                                        <td>
                                            @if ($repo->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">{{ ucfirst($repo->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($repo->url)
                                                <a href="{{ $repo->url }}" target="_blank" class="btn btn-sm btn-link"><i
                                                        class="fas fa-external-link-alt"></i> Open URL</a>
                                            @else
                                                -
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
                                @endforeach
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
            $('#repository-datatables').DataTable({});
        });
    </script>
@endpush
