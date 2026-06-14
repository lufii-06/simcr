@extends('dashboard')

@section('title', 'Laporan Management')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Laporan Project, Client & Developer</h4>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Filter -->
                    <form action="{{ route('report.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari Project atau Client..." value="{{ $search }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if($search)
                                    <a href="{{ route('report.index') }}" class="btn btn-secondary">Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover mt-3">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td>{{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}</td>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->client?->user?->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $project->status?->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $project->start_date }}</td>
                                        <td>{{ $project->end_date ?? '-' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary btn-detail" data-id="{{ $project->getRouteKey() }}">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Data laporan tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $projects->appends(['search' => $search])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-detail', function() {
            const id = $(this).data('id');
            // Reuse existing global detail modal logic
            if (typeof window.showDetail === 'function') {
                window.showDetail(id, 'project');
            } else {
                // Fallback or manual trigger
                $.get(`/project/${id}`, function(data) {
                    $('#detailModal .modal-title').html('Detail Project: ' + data.name);
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Client:</strong> ${data.client?.user?.name || 'N/A'}</p>
                                <p><strong>Status:</strong> ${data.status?.name || 'N/A'}</p>
                                <p><strong>Start Date:</strong> ${data.start_date || '-'}</p>
                                <p><strong>End Date:</strong> ${data.end_date || '-'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Owner:</strong> ${data.owner?.name || 'N/A'}</p>
                                <p><strong>Description:</strong> ${data.description || '-'}</p>
                            </div>
                        </div>
                    `;
                    $('#detailModal .modal-body').html(html);
                    $('#detailModal').modal('show');
                });
            }
        });
    });
</script>
@endpush
