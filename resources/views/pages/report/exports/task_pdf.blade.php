<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
        .kop table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 120px; }
        .company-info { text-align: right; }
        .company-info h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .company-info p { margin: 2px 0; font-size: 9pt; }
        .report-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 30px; text-decoration: underline; }
        .year-info { margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; text-align: center; text-transform: uppercase; font-size: 9pt; }
        .ttd-container { margin-top: 50px; float: right; width: 250px; text-align: center; }
        .ttd-space { height: 70px; }
    </style>
</head>
<body>
    <div class="kop">
        <table width="100%">
            <tr>
                <td width="20%">
                    @if(file_exists(public_path('images/logo-black.png')))
                        <img src="{{ public_path('images/logo-black.png') }}" class="logo">
                    @else
                        <div style="font-weight: bold; font-size: 20pt;">LOGO</div>
                    @endif
                </td>
                <td width="80%" class="company-info">
                    <h1>PT. ARG SOLUSI TEKNOLOGI</h1>
                    <p>Jalan puti bungsi 17B, jl. Belanti Permai 2 No.Kav 2, Kota Padang, Sumatera Barat 25173</p>
                    <p>contact: +6283182000000, Email: argenesiacom@gmail.com</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        LAPORAN DATA TASKS
    </div>

    <div class="year-info">
        @if(isset($project_name) && $project_name) Project : {{ $projectName }} @endif
        @if(isset($assignee_name) && $assignee_name) | Assignee : {{ $assignee_name }} @endif
        @if(isset($status_name) && $status_name) | Status : {{ $status_name }} @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="80">Kode</th>
                <th width="150">Project</th>
                <th>Judul Task</th>
                <th width="120">Assignee</th>
                <th width="80">Status</th>
                <th width="60">Prog.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center">{{ $row->code }}</td>
                    <td>{{ $row->project?->name ?? '-' }}</td>
                    <td>{{ $row->title }}</td>
                    <td>{{ $row->assignee?->name ?? '-' }}</td>
                    <td align="center">{{ strtoupper($row->status?->name ?? '-') }}</td>
                    <td align="center">{{ round($row->progress) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" align="center">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-container">
        <div>PADANG, {{ date('Y-m-d') }}</div>
        <div>Pimpinan,</div>
        <div class="ttd-space"></div>
        <div>(..................................................)</div>
    </div>
</body>
</html>
