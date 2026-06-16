<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Tugas Pribadi – {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        .kop table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
        }

        .company-info {
            text-align: right;
        }

        .company-info h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 9pt;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 20px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .table-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table-details td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 9pt;
        }

        .badge {
            display: inline-block;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .ttd-container {
            margin-top: 40px;
            float: right;
            width: 250px;
            text-align: center;
            font-size: 9pt;
        }

        .ttd-space {
            height: 60px;
        }

        .checklist-item {
            font-size: 8.5pt;
            padding: 2px 0;
        }

        .checklist-done {
            text-decoration: line-through;
            color: #888;
        }
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
        LAPORAN TUGAS PRIBADI
    </div>

    {{-- ========= DETAIL USER ========= --}}
    <div class="section-title">Informasi Pengguna</div>
    <table class="table-details" style="border: 1px solid #000;">
        <tr>
            <td width="25%"
                style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                Nama Pengembang</td>
            <td width="75%" style="border-bottom: 1px solid #000;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td
                style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                Role / Peran</td>
            <td style="border-bottom: 1px solid #000;">{{ strtoupper($user->role) }}</td>
        </tr>
        <tr>
            <td
                style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                Total Tugas</td>
            <td style="border-bottom: 1px solid #000;">{{ $totalTasks }} Tugas</td>
        </tr>
        <tr>
            <td
                style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                Tugas Selesai</td>
            <td style="border-bottom: 1px solid #000;">{{ $doneTasks }} Tugas</td>
        </tr>
        <tr>
            <td
                style="font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                Tugas Pending</td>
            <td style="border-bottom: 1px solid #000;">{{ $pendingTasks }} Tugas</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-right: 1px solid #000; background-color: #f9f9f9;">Tingkat Penyelesaian
            </td>
            <td><strong>{{ $globalRate }}%</strong></td>
        </tr>
    </table>

    {{-- ========= DETAIL TASKS ========= --}}
    <div class="section-title">Detail Tugas (Tasks)</div>
    <table class="table">
        <thead>
            <tr>
                <th width="50">Kode</th>
                <th width="160">Judul Tugas (Task)</th>
                <th width="80">Project</th>
                <th width="65">Status</th>
                <th width="30" style="text-align: center;">Checklist</th>
                <th width="30" style="text-align: center;">Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                @php
                    $checkTotal = $task->checklists->count();
                    $checkComplete = $task->checklists->where('is_completed', true)->count();
                    $statusName = strtolower($task->status->name ?? '');
                    $progress = $checkTotal > 0 ? round(($checkComplete / $checkTotal) * 100) : ($statusName === 'done' ? 100 : 0);
                @endphp
                <tr>
                    <td align="center"><code>{{ $task->code ?? '-' }}</code></td>
                    <td>
                        {{ $task->title }}
                        @if($task->description)
                            <div style="font-size: 8pt; color: #555; margin-top: 2px;">{{ Str::limit($task->description, 100) }}
                            </div>
                        @endif
                        @if($checkTotal > 0)
                            <div style="margin-top: 6px; padding-left: 8px;">
                                @foreach($task->checklists as $item)
                                    <div class="checklist-item {{ $item->is_completed ? 'checklist-done' : '' }}"
                                        style="margin-bottom: 2px;">
                                        <strong>[{{ $item->code }}]</strong> {{ $item->item_text }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>{{ $task->project?->name ?? '-' }}</td>
                    <td align="center">{{ strtoupper($task->status?->name ?? '-') }}</td>
                    <td align="center">{{ $checkComplete }}/{{ $checkTotal }}</td>
                    <td align="center"><strong>{{ $progress }}%</strong></td>
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