@extends('dashboard')

@section('title', 'Laporan Projects')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Preview Data Project</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#printModal">
                            <i class="fa fa-print"></i>
                            Cetak Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Preview -->
                    <form action="{{ route('report.project') }}" method="GET" class="row mb-4">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Klien</label>
                                <select name="client_id" class="form-select form-control">
                                    <option value="">Semua Klien</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Kata Kunci</label>
                                <input type="text" name="keyword" class="form-control" placeholder="Cari nama, kode..." value="{{ $keyword }}">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
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
                                    <th>Nama Project</th>
                                    <th>Klien</th>
                                    <th>Status</th>
                                    <th>Tgl Mulai</th>
                                    <th>Tgl Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><code>{{ $item->code }}</code></td>
                                        <td class="fw-bold">{{ $item->name }}</td>
                                        <td>{{ $item->client->company_name }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $item->status->color ?? '#eee' }}; color: #fff;">
                                                {{ $item->status->name }}
                                            </span>
                                        </td>
                                        <td>{{ $item->start_date ? date('d/m/Y', strtotime($item->start_date)) : '-' }}</td>
                                        <td>{{ $item->end_date ? date('d/m/Y', strtotime($item->end_date)) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Data project tidak ditemukan</td>
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
                    <input type="hidden" name="type" value="project">
                    <div class="modal-body">
                        <p class="small text-muted">Sesuaikan parameter sebelum mencetak laporan.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filter Klien</label>
                                    <select name="client_id" class="form-select form-control">
                                        <option value="">Semua Klien</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control" placeholder="Cari nama, kode..." value="{{ $keyword }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Periode Dari</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Periode Sampai</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
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
