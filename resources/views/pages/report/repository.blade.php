@extends('dashboard')

@section('title', 'Laporan Repository')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Preview Data Repository</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#printModal">
                            <i class="fa fa-print"></i>
                            Cetak Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Preview -->
                    <form action="{{ route('report.repository') }}" method="GET" class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Project</label>
                                <select name="project_id" class="form-select form-control">
                                    <option value="">Semua Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Visibilitas</label>
                                <select name="visibility" class="form-select form-control">
                                    <option value="">Semua</option>
                                    <option value="public" {{ $visibility == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ $visibility == 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-select form-control">
                                    <option value="">Semua</option>
                                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="archived" {{ $status == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-secondary btn-block">Filter Preview</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-head-bg-primary">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Repository</th>
                                    <th>Project</th>
                                    <th>Visibilitas</th>
                                    <th>URL Clone (HTTP)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $item->name }}</td>
                                        <td>{{ $item->project->name }}</td>
                                        <td>
                                            @if($item->is_public)
                                                <span class="badge badge-success"><i class="fa fa-globe"></i> Public</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fa fa-lock"></i> Private</span>
                                            @endif
                                        </td>
                                        <td><code>{{ config('app.url') }}/git/{{ $item->name }}.git</code></td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="badge badge-primary">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Archived</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Data repository tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold"> Parameter</span>
                        <span class="fw-light"> Cetak </span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="printForm" action="" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="type" value="repository">
                    <div class="modal-body">
                        <p class="small text-muted">Sesuaikan parameter sebelum mencetak laporan.</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Filter Project</label>
                                    <select name="project_id" class="form-select form-control">
                                        <option value="">Semua Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filter Visibilitas</label>
                                    <select name="visibility" class="form-select form-control">
                                        <option value="">Semua</option>
                                        <option value="public">Public</option>
                                        <option value="private">Private</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filter Status</label>
                                    <select name="status" class="form-select form-control">
                                        <option value="">Semua</option>
                                        <option value="active">Active</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" onclick="submitExport('pdf')" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Cetak PDF
                        </button>
                        <button type="button" onclick="submitExport('excel')" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Ekspor Excel
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitExport(format) {
            const form = document.getElementById('printForm');
            if (format === 'pdf') {
                form.action = "{{ route('report.export.pdf') }}";
            } else {
                form.action = "{{ route('report.export.excel') }}";
            }
            form.submit();
        }
    </script>
@endpush
