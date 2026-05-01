@extends('dashboard')

@section('title', 'User Management')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">User List</h4>
                        <a href="{{ route('user.create') }}" class="btn btn-primary btn-round ms-auto">
                            <i class="fa fa-plus"></i>
                            Add User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="user-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="avatar">
                                                <img src="{{ $user->getAvatarUrl() }}" alt="..."
                                                    class="avatar-img rounded-circle">
                                            </div>
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                                        </td>
                                        <td>{{ $user->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $user->getRouteKey() }}" data-type="user" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('user.edit', $user) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit User">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('user.reset-password', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to reset password to a random string?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link btn-warning btn-lg"
                                                        data-bs-toggle="tooltip" title="Reset Password">
                                                        <i class="fa fa-key"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('user.destroy', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?')">
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
            $('#user-datatables').DataTable({});

            $('.btn-detail').on('click', function() {
                var id = $(this).data('id');
                var modal = $('#detailModal');
                $('#detailModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                    );
                modal.modal('show');

                $.get("{{ url('user') }}/" + id, function(data) {
                    var user = data.user;
                    var html = `
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="${data.avatar_url}" class="img-fluid rounded-circle border p-1 mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr><th width="30%">Name</th><td>${user.name}</td></tr>
                                    <tr><th>Email</th><td>${user.email}</td></tr>
                                    <tr><th>Role</th><td><span class="badge badge-info">${user.role.toUpperCase()}</span></td></tr>
                                    <tr><th>Created At</th><td>${new Date(user.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})}</td></tr>
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
