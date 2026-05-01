@extends('dashboard')

@section('title', 'Project Status Settings')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Project Status List</h4>
                        @if (count($statuses) < 5)
                            <a href="{{ route('project-status.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Add Status
                            </a>
                        @else
                            <button class="btn btn-secondary btn-round ms-auto" disabled>
                                <i class="fa fa-plus"></i>
                                Limit Reached (5)
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="status-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statuses as $status)
                                    <tr>
                                        <td>{{ $status->name }}</td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $status->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('project-status.edit', $status) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('project-status.destroy', $status) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link btn-danger"
                                                        data-bs-toggle="tooltip" title="Remove">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
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
            $('#status-datatables').DataTable({});

            $('.btn-detail').on('click', function() {
                var id = $(this).data('id');
                var modal = $('#detailModal');
                $('#detailModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                    );
                modal.modal('show');

                $.get("{{ url('setting/project-status') }}/" + id, function(data) {
                    var html = `
                        <table class="table table-bordered">
                            <tr><th width="30%">Status ID</th><td>${data.id}</td></tr>
                            <tr><th>Status Name</th><td><span class="badge badge-info">${data.name}</span></td></tr>
                            <tr><th>Created At</th><td>${new Date(data.created_at).toLocaleString('id-ID')}</td></tr>
                        </table>
                    `;
                    $('#detailModalBody').html(html);
                });
            });
        });
    </script>
@endpush
