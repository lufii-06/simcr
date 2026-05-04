<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #444;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #1a1a1a;
        }
        .header p {
            margin: 5px 0;
            font-size: 10pt;
            color: #666;
        }
        .report-info {
            margin-bottom: 20px;
        }
        .report-info table {
            width: 100%;
        }
        .report-info td {
            font-size: 10pt;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10pt;
        }
        .table td {
            font-size: 10pt;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8pt;
            background: #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMCR - Laporan Master Data</h1>
        <p>Sistem Informasi Management Code Repository</p>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td width="150"><strong>Jenis Laporan</strong></td>
                <td>: Master User</td>
                <td width="150" align="right"><strong>Dicetak Pada</strong></td>
                <td width="150">: {{ date('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Periode</strong></td>
                <td>: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</td>
                <td align="right"><strong>Filter Role</strong></td>
                <td>: {{ $filters['role'] ? strtoupper($filters['role']) : 'SEMUA' }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
                <th>Tanggal Gabung</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $user)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ strtoupper($user->role) }}</td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" align="center">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan SIMCR - Halaman 1 dari 1
    </div>
</body>
</html>
