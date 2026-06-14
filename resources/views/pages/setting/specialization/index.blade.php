@extends('dashboard')

@section('title', 'Specialization Settings')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Specialization List</h4>
                        <a href="{{ route('specialization.create') }}" class="btn btn-primary btn-round ms-auto">
                            <i class="fa fa-plus"></i>
                            Add Specialization
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="specialization-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Used By</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($specializations as $item)
                                    <tr>
                                        <td>{{ $item->name ?? 'N/A' }}</td>
                                        <td class="text-white">
                                            <span class="badge badge-count badge-primary">{{ optional($item->developers())->count() ?? 0 }} Developers</span>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $item->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('specialization.edit', $item) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('specialization.destroy', $item) }}"
                                                    method="POST" class="d-inline form-delete">
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
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No specializations found.</td>
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
            $('#specialization-datatables').DataTable({});

            $(document).on('click', '.btn-detail', function() {
                var id = $(this).data('id');
                var modal = $('#detailModal');
                $('#detailModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                    );
                modal.modal('show');

                $.get("{{ url('setting/specialization') }}/" + id, function(data) {
                    var html = `
                        <table class="table table-bordered">
                            <tr><th width="30%">ID</th><td>${data.id}</td></tr>
                            <tr><th>Name</th><td><span class="badge badge-info">${data.name}</span></td></tr>
                            <tr><th>Created At</th><td>${new Date(data.created_at).toLocaleString('id-ID')}</td></tr>
                        </table>
                    `;
                    $('#detailModalBody').html(html);
                });
            });

            // Delete Confirmation
            $(document).on('submit', '.form-delete', function(e) {
                e.preventDefault();
                var form = this;
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this specialization!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
