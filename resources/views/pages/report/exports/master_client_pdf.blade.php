<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
            margin-bottom: 30px;
            text-decoration: underline;
        }
        .year-info {
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .ttd-container {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .ttd-space {
            height: 70px;
        }
    </style>
</head>
<body>
    <div class="kop">
        <table width="100%">
            <tr>
                <td width="20%">
                    @if(file_exists(public_path('images/logo-color.png')))
                        <img src="{{ public_path('images/logo-color.png') }}" class="logo">
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
        LIST CLIENT
    </div>

    <div class="year-info">
        Tahun : {{ $filters['start_date'] ? date('Y', strtotime($filters['start_date'])) : date('Y') }} - {{ $filters['end_date'] ? date('Y', strtotime($filters['end_date'])) : date('Y') }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="150">Nama Perusahaan</th>
                <th width="120">Kontak Utama</th>
                <th width="150">Email</th>
                <th width="100">Nohp</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $client)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td>{{ $client->company_name }}</td>
                    <td>{{ $client->main_contact }}</td>
                    <td>{{ $client->user->email }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ $client->address }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" align="center">Data tidak ditemukan</td>
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
