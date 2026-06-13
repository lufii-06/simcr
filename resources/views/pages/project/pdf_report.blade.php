<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Detail Project – {{ $project->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
        .kop table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 120px; }
        .company-info { text-align: right; }
        .company-info h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .company-info p { margin: 2px 0; font-size: 9pt; }
        
        .report-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 20px; text-decoration: underline; text-transform: uppercase; }
        .section-title { font-weight: bold; font-size: 11pt; margin-top: 20px; margin-bottom: 8px; text-transform: uppercase; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table th, .table td { border: 1px solid #000; padding: 6px 8px; text-align: left; font-size: 9pt; }
        .table th { background-color: #f2f2f2; text-align: center; text-transform: uppercase; font-size: 9pt; }
        
        .table-details { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-details td { padding: 5px 8px; vertical-align: top; font-size: 9pt; }
        
        .badge { display: inline-block; font-weight: bold; text-transform: uppercase; font-size: 8pt; }
        
        .ttd-container { margin-top: 40px; float: right; width: 250px; text-align: center; font-size: 9pt; }
        .ttd-space { height: 60px; }
    </style>
</head>
<body>
    {{-- ========= KOP SURAT ========= --}}
    <div class="kop">
        <table width="100%" style="border: none;">
            <tr style="border: none;">
                <td width="20%" style="border: none; padding: 0;">
                    @if(file_exists(public_path('images/logo-black.png')))
                        <img src="{{ public_path('images/logo-black.png') }}" class="logo">
                    @else
                        <div style="font-weight: bold; font-size: 20pt;">LOGO</div>
                    @endif
                </td>
                <td width="80%" class="company-info" style="border: none; padding: 0;">
                    <h1>PT. ARG SOLUSI TEKNOLOGI</h1>
                    <p>Jalan puti bungsi 17B, jl. Belanti Permai 2 No.Kav 2, Kota Padang, Sumatera Barat 25173</p>
                    <p>contact: +6283182000000, Email: argenesiacom@gmail.com</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- ========= JUDUL LAPORAN ========= --}}
    <div class="report-title">
        LAPORAN DETAIL PROJECT
    </div>

    {{-- ========= DETAIL PROJECT ========= --}}
    <div class="section-title">Informasi Project</div>
    <table class="table-details" style="border: 1px solid #000;">
        <tr>
            <td width="25%" style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Nama Project</td>
            <td width="75%" style="border-bottom: 1px solid #000;">{{ $project->name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Kode Project</td>
            <td style="border-bottom: 1px solid #000;">{{ $project->code }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Klien</td>
            <td style="border-bottom: 1px solid #000;">{{ $project->client?->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Project Manager</td>
            <td style="border-bottom: 1px solid #000;">{{ $project->owner?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Status</td>
            <td style="border-bottom: 1px solid #000;">{{ strtoupper($project->status?->name ?? '-') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Timeline</td>
            <td style="border-bottom: 1px solid #000;">
                @if ($project->start_date && $project->end_date)
                    {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">Repository</td>
            <td style="border-bottom: 1px solid #000;">{{ $project->repository?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; background-color: #f9f9f9;">Deskripsi</td>
            <td>{{ $project->description ?: '-' }}</td>
        </tr>
    </table>

    {{-- ========= TIM / DEVELOPER ========= --}}
    <div class="section-title">Tim Pengembang (Developers)</div>
    <table class="table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>Nama Developer</th>
                <th width="120">Peran (Role)</th>
                <th width="70" style="text-align: center;">Total Task</th>
                <th width="70" style="text-align: center;">Task Selesai</th>
                <th width="70" style="text-align: center;">Task Pending</th>
                <th width="70" style="text-align: center;">Git Commits</th>
                <th width="80" style="text-align: center;">Completion</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($developerStats as $ds)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td>{{ $ds['name'] }}</td>
                    <td>{{ $ds['role'] }}</td>
                    <td align="center">{{ $ds['total_tasks'] }}</td>
                    <td align="center">{{ $ds['done_tasks'] }}</td>
                    <td align="center">{{ $ds['pending_tasks'] }}</td>
                    <td align="center">{{ $ds['commit_count'] }}</td>
                    <td align="center"><strong>{{ $ds['completion_rate'] }}%</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" align="center">Tidak ada developer ditugaskan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========= DETAIL TASKS ========= --}}
    <div class="section-title">Detail Tugas (Tasks)</div>
    <table class="table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="80">Kode</th>
                <th>Judul Tugas (Task)</th>
                <th width="150">Ditugaskan Kepada</th>
                <th width="100">Status</th>
                <th width="80" style="text-align: center;">Checklist</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->tasks as $task)
                @php
                    $checkTotal = $task->checklists->count();
                    $checkComplete = $task->checklists->where('is_completed', true)->count();
                @endphp
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center"><code>{{ $task->code }}</code></td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->assignee?->name ?? '-' }}</td>
                    <td align="center">{{ strtoupper($task->status?->name ?? '-') }}</td>
                    <td align="center">{{ $checkComplete }}/{{ $checkTotal }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" align="center">Tidak ada task ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========= TANDA TANGAN ========= --}}
    <div class="ttd-container">
        <div>PADANG, {{ date('d F Y') }}</div>
        <div>Pimpinan,</div>
        <div class="ttd-space"></div>
        <div>(..................................................)</div>
    </div>
</body>
</html>
