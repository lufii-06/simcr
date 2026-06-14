@extends('dashboard')

@section('title', 'Laporan Tasks')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Preview Data Task</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#printModal">
                            <i class="fa fa-print"></i>
                            Cetak Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Preview -->
                    <form action="{{ route('report.task') }}" method="GET" class="row mb-4">
                        <div class="col-md-2">
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
                                <label>Assignee</label>
                                <select name="assigned_to" class="form-select form-control">
                                    <option value="">Semua Developer</option>
                                    @foreach($developers as $dev)
                                        <option value="{{ $dev->user_id }}" {{ $assigneeId == $dev->user_id ? 'selected' : '' }}>{{ $dev->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status_id" class="form-select form-control">
                                    <option value="">Semua Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $statusId == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Kata Kunci</label>
                                <input type="text" name="keyword" class="form-control" placeholder="Cari judul, kode..." value="{{ $keyword }}">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
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
                                    <th>Kode</th>
                                    <th>Project</th>
                                    <th>Judul Task</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><code>{{ $item->code }}</code></td>
                                        <td>{{ $item->project?->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $item->title }}</td>
                                        <td>{{ $item->assignee?->name ?? 'Unassigned' }}</td>
                                        <td>
                                            @php
                                                $statusName = $item->status?->name ?? '';
                                                $badgeClass = 'badge-info';
                                                if ($statusName === 'To Do') {
                                                    $badgeClass = 'badge-primary';
                                                } elseif ($statusName === 'In Progress') {
                                                    $badgeClass = 'badge-warning';
                                                } elseif ($statusName === 'Done') {
                                                    $badgeClass = 'badge-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $statusName }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item->progress }}%" aria-valuenow="{{ $item->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small>{{ round($item->progress) }}%</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Data task tidak ditemukan</td>
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
                    <input type="hidden" name="type" value="task">
                    <div class="modal-body">
                        <p class="small text-muted">Sesuaikan parameter sebelum mencetak laporan.</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control" placeholder="Cari judul, kode..." value="{{ $keyword }}">
                                </div>
                            </div>
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
                                    <label>Filter Assignee</label>
                                    <select name="assigned_to" class="form-select form-control">
                                        <option value="">Semua Developer</option>
                                        @foreach($developers as $dev)
                                            <option value="{{ $dev->user_id }}" {{ $assigneeId == $dev->user_id ? 'selected' : '' }}>{{ $dev->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filter Status</label>
                                    <select name="status_id" class="form-select form-control">
                                        <option value="">Semua Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $statusId == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
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
