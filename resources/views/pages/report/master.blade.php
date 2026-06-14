@extends('dashboard')

@section('title', 'Laporan Master Data')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Preview Data Master</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                            data-bs-target="#printModal">
                            <i class="fa fa-print"></i>
                            Cetak Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $category == 'client' ? 'active' : '' }}"
                                href="{{ route('report.master', ['category' => 'client']) }}">
                                <i class="fas fa-users me-1"></i> List Client
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $category == 'developer' ? 'active' : '' }}"
                                href="{{ route('report.master', ['category' => 'developer']) }}">
                                <i class="fas fa-user-tag me-1"></i> List Developer
                            </a>
                        </li>
                    </ul>

                    <!-- Filter Preview -->
                    <form action="{{ route('report.master') }}" method="GET" class="row mb-4">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <div class="{{ $category == 'developer' ? 'col-md-2' : 'col-md-3' }}">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="{{ $category == 'developer' ? 'col-md-2' : 'col-md-3' }}">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                        </div>
                        @if($category == 'developer')
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Spesialisasi</label>
                                    <select name="specialization_id" class="form-select form-control">
                                        <option value="">Semua Spesialisasi</option>
                                        @foreach($specializations as $spec)
                                            <option value="{{ $spec->id }}" {{ $specializationId == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="{{ $category == 'developer' ? 'col-md-3' : 'col-md-3' }}">
                            <div class="form-group">
                                <label>Kata Kunci</label>
                                <input type="text" name="keyword" class="form-control" placeholder="Cari nama, email..." value="{{ $keyword }}">
                            </div>
                        </div>
                        <div class="{{ $category == 'developer' ? 'col-md-3' : 'col-md-3' }} d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-secondary btn-block">Filter Preview</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-head-bg-primary">
                            <thead>
                                @if($category == 'client')
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Kontak Utama</th>
                                        <th>Email</th>
                                        <th>Nohp</th>
                                        <th>Alamat</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Developer</th>
                                        <th>Spesialisasi</th>
                                        <th>Email</th>
                                        <th>Nohp</th>
                                        <th>Alamat</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    @if($category == 'client')
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-bold">{{ $item->company_name }}</td>
                                            <td>{{ $item->main_contact }}</td>
                                            <td>{{ $item->user?->email ?? '-' }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->address }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-bold">{{ $item->user?->name ?? '-' }}</td>
                                            <td><span class="badge badge-info">{{ $item->specialization?->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $item->user?->email ?? '-' }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->address }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Data {{ $category }} tidak ditemukan</td>
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
                    <input type="hidden" name="type" value="master">
                    <div class="modal-body">
                        <p class="small text-muted">Sesuaikan parameter sebelum mencetak laporan.</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kategori Master</label>
                                    <select name="category" id="modalCategory" class="form-select form-control" onchange="toggleModalSpec()">
                                        <option value="client" {{ $category == 'client' ? 'selected' : '' }}>List Client
                                        </option>
                                        <option value="developer" {{ $category == 'developer' ? 'selected' : '' }}>List
                                            Developer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12" id="modalSpecGroup" style="{{ $category == 'client' ? 'display:none' : '' }}">
                                <div class="form-group">
                                    <label>Filter Spesialisasi</label>
                                    <select name="specialization_id" class="form-select form-control">
                                        <option value="">Semua Spesialisasi</option>
                                        @foreach($specializations as $spec)
                                            <option value="{{ $spec->id }}" {{ $specializationId == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control" placeholder="Cari nama, email..." value="{{ $keyword }}">
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
        function toggleModalSpec() {
            const cat = document.getElementById('modalCategory').value;
            const specGroup = document.getElementById('modalSpecGroup');
            if (cat === 'developer') {
                specGroup.style.display = 'block';
            } else {
                specGroup.style.display = 'none';
            }
        }

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