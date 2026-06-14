@extends('dashboard')

@section('title', 'Developer Management')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Developer Profiles</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="developer-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name (User)</th>
                                    <th>Specialization</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Portfolio</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($developers as $developer)
                                    <tr>
                                        <td>{{ $developer->user->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-primary">{{ $developer->specialization->name ?? 'General' }}</span></td>
                                        <td>{{ $developer->phone ?? '-' }}</td>
                                        <td>{{ Str::limit($developer->address ?? '-', 30) }}</td>
                                        <td>
                                            @if ($developer->portfolio_url ?? false)
                                                <a href="{{ $developer->portfolio_url }}" target="_blank"
                                                    class="btn btn-xs btn-outline-info">View Link</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $developer->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('developer.performance', $developer) }}"
                                                    class="btn btn-link btn-success btn-lg" data-bs-toggle="tooltip"
                                                    title="View Performance">
                                                    <i class="fa fa-chart-bar"></i>
                                                </a>
                                                <a href="{{ route('developer.edit', $developer) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit Profile">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('developer.destroy', $developer) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Deleting the developer will also delete the associated user account. Are you sure?')">
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
                                        <td colspan="6" class="text-center py-4 text-muted">No developers found.</td>
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
            $('#developer-datatables').DataTable({});

            $('.btn-detail').on('click', function() {
                var id = $(this).data('id');
                var modal = $('#detailModal');
                $('#detailModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                    );
                modal.modal('show');

                $.get("{{ url('developer') }}/" + id, function(data) {
                    var dev = data.developer;
                    var user = data.user;
                    var html = `
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="{{ asset('images/user-default.png') }}" class="img-fluid rounded-circle border p-1 mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <h5 class="fw-bold text-primary mb-3">Developer Information</h5>
                                <table class="table table-bordered">
                                    <tr><th width="30%">Account Name</th><td>${user ? user.name : 'N/A'}</td></tr>
                                    <tr><th>Email</th><td>${user ? user.email : 'N/A'}</td></tr>
                                    <tr><th>Specialization</th><td><span class="badge badge-primary">${data.specialization ? data.specialization.name : 'General'}</span></td></tr>
                                    <tr><th>Phone</th><td>${dev.phone || '-'}</td></tr>
                                    <tr><th>Address</th><td>${dev.address || '-'}</td></tr>
                                    <tr><th>Portfolio</th><td>${dev.portfolio_url ? `<a href="${dev.portfolio_url}" target="_blank">${dev.portfolio_url}</a>` : '-'}</td></tr>
                                    <tr><th>Joined At</th><td>${dev.created_at ? new Date(dev.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-'}</td></tr>
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
