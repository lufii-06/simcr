@extends('dashboard')

@section('title', 'Client Management')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Client Profiles</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="client-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name (User)</th>
                                    <th>Company</th>
                                    <th>Contact</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $client)
                                    <tr>
                                        <td>{{ $client->user->name ?? 'N/A' }}</td>
                                        <td>{{ $client->company_name ?? '-' }}</td>
                                        <td>{{ $client->main_contact ?? '-' }}</td>
                                        <td>{{ $client->phone ?? '-' }}</td>
                                        <td>{{ Str::limit($client->address ?? '-', 30) }}</td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $client->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('client.edit', $client) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit Profile">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('client.destroy', $client) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Deleting the client will also delete the associated user account. Are you sure?')">
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
                                        <td colspan="6" class="text-center py-4 text-muted">No clients found.</td>
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
            $('#client-datatables').DataTable({});

            $('.btn-detail').on('click', function() {
                var id = $(this).data('id');
                var modal = $('#detailModal');
                $('#detailModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                    );
                modal.modal('show');

                $.get("{{ url('client') }}/" + id, function(data) {
                    var client = data.client;
                    var user = data.user;
                    var html = `
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="fw-bold text-primary mb-3">Client Information</h5>
                                <table class="table table-bordered">
                                    <tr><th width="30%">Account Name</th><td>${user ? user.name : 'N/A'}</td></tr>
                                    <tr><th>Email</th><td>${user ? user.email : 'N/A'}</td></tr>
                                    <tr><th>Company Name</th><td>${client.company_name}</td></tr>
                                    <tr><th>Main Contact</th><td>${client.main_contact}</td></tr>
                                    <tr><th>Phone</th><td>${client.phone}</td></tr>
                                    <tr><th>Address</th><td>${client.address}</td></tr>
                                    <tr><th>Registered At</th><td>${new Date(client.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})}</td></tr>
                                </table>
                            </div>
                        </div>
                    `;
                    $('#detailModalBody').html(html);
                });
            });
        });
    </script>
@endpush
