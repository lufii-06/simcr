@extends('dashboard')

@section('title', 'Project Management')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Project List</h4>
                        <a href="{{ route('project.create') }}" class="btn btn-primary btn-round ms-auto">
                            <i class="fa fa-plus"></i>
                            Add Project
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="status-filter">Filter by Status:</label>
                            <select id="status-filter" class="form-control">
                                <option value="">All Status</option>
                                @foreach($projects->pluck('status.name')->unique() as $statusName)
                                    @if($statusName)
                                        <option value="{{ $statusName }}">{{ $statusName }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="project-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Client</th>
                                    <th>Owner (PM)</th>
                                    <th>Status</th>
                                    <th>Timeline</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td>{{ Str::limit($project->name ?? 'Untitled Project', 30) }}</td>
                                        <td>{{ $project->client?->user?->name ?? 'N/A' }}</td>
                                        <td>{{ $project->owner->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-info">{{ $project->status?->name ?? 'N/A' }}</span></td>
                                        <td>
                                            @if (($project->start_date ?? false) && ($project->end_date ?? false))
                                                {{ \Carbon\Carbon::parse($project->start_date)->format('d M y') }} -
                                                {{ \Carbon\Carbon::parse($project->end_date)->format('d M y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $project->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('project.edit', $project) }}"
                                                    class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip"
                                                    title="Edit Project">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('project.destroy', $project) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this project?')">
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
                                        <td colspan="6" class="text-center py-4 text-muted">No projects found.</td>
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
        function openProjectModal(id) {
            var modal = $('#detailModal');
            $('#detailModalBody').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );

            // Ensure the export pdf button exists in footer
            var printBtn = $('#btn-export-project-pdf');
            if (printBtn.length === 0) {
                $('#detailModal .modal-footer').prepend(
                    '<a id="btn-export-project-pdf" href="#" class="btn btn-primary" target="_blank"><i class="fas fa-print me-1"></i> Cetak Project</a>'
                );
                printBtn = $('#btn-export-project-pdf');
            }
            printBtn.hide();

            modal.modal('show');

            $.get("{{ url('project') }}/" + id, function(data) {
                var devsHtml = '';
                if (data.developers && data.developers.length > 0) {
                    data.developers.forEach(function(dev) {
                        var userName = dev.user ? dev.user.name : 'Unknown';
                        var roleName = dev.role ? dev.role.name : 'Unknown';
                        devsHtml +=
                            `<tr><td>${userName}</td><td><span class="badge badge-secondary">${roleName}</span></td></tr>`;
                    });
                } else {
                    devsHtml =
                        `<tr><td colspan="2" class="text-center">No developers assigned</td></tr>`;
                }

                var html = `
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="fw-bold text-primary mb-3">Project Details</h5>
                            <table class="table table-bordered">
                                <tr><th width="30%">Project Name</th><td>${data.name}</td></tr>
                                <tr><th>Client</th><td>${data.client && data.client.user ? data.client.user.name : '-'}</td></tr>
                                <tr><th>Owner (PM)</th><td>${data.owner ? data.owner.name : '-'}</td></tr>
                                <tr><th>Project Status</th><td><span class="badge badge-info">${data.status ? data.status.name : '-'}</span></td></tr>
                                <tr><th>Start Date</th><td>${data.start_date || '-'}</td></tr>
                                <tr><th>End Date</th><td>${data.end_date || '-'}</td></tr>
                                <tr><th>Description</th><td>${data.description ? data.description.replace(/\n/g, '<br>') : '-'}</td></tr>
                                <tr>
                                    <th>Repository</th>
                                    <td>
                                        ${data.repository ? `
                                            <a href="{{ url('repository') }}/${data.repository.name}" class="btn btn-primary btn-sm btn-round text-white">
                                                <i class="fas fa-code me-1"></i> Go to Repository
                                            </a>
                                        ` : '<span class="text-muted">-</span>'}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12 mt-4">
                            <h5 class="fw-bold text-primary mb-3">Assigned Team</h5>
                            <table class="table table-hover table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Developer Name</th>
                                        <th>Project Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${devsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                $('#detailModalBody').html(html);

                // Set the PDF export link in modal footer
                var pdfUrl = "{{ url('project') }}/" + id + "/export-pdf";
                $('#btn-export-project-pdf').attr('href', pdfUrl).show();
            });
        }

        $(document).ready(function() {
            var table = $('#project-datatables').DataTable({});

            $('#status-filter').on('change', function() {
                var val = $(this).val();
                table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            $(document).on('click', '.btn-detail', function() {
                var id = $(this).data('id');
                openProjectModal(id);
            });

            // Global search auto-open modal
            const urlParams = new URLSearchParams(window.location.search);
            const showId = urlParams.get('show_id');
            if (showId) {
                openProjectModal(showId);
            }
        });
    </script>
@endpush
