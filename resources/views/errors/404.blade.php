<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Halaman Tidak Ditemukan | SIMCR</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2a2f43;
        }

        .error-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 64px;
            max-width: 900px;
            width: 100%;
            padding: 40px 24px;
        }

        /* ——— Illustration ——— */
        .error-illustration {
            flex-shrink: 0;
            width: 280px;
        }

        /* ——— Content ——— */
        .error-content {
            max-width: 420px;
        }

        .error-badge {
            display: inline-block;
            background: #eff4ff;
            color: #1572E8;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #d0e2fb;
            margin-bottom: 16px;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            line-height: 1;
            color: #1572E8;
            letter-spacing: -3px;
            margin-bottom: 8px;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a2035;
            margin-bottom: 12px;
        }

        .error-desc {
            font-size: 0.95rem;
            color: #6c757d;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            background: #1572E8;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s ease, transform 0.15s ease;
        }
        .btn-primary-custom:hover {
            background: #1260c4;
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-secondary-custom {
            background: #fff;
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 10px 24px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: border-color 0.2s ease, color 0.2s ease;
        }
        .btn-secondary-custom:hover {
            border-color: #adb5bd;
            color: #495057;
            text-decoration: none;
        }

        .error-divider {
            width: 48px;
            height: 3px;
            background: #1572E8;
            border-radius: 2px;
            margin-bottom: 20px;
        }

        @media (max-width: 720px) {
            .error-wrap { flex-direction: column-reverse; gap: 32px; text-align: center; }
            .error-illustration { width: 200px; }
            .error-actions { justify-content: center; }
            .error-divider { margin: 0 auto 20px; }
        }
    </style>
</head>
<body>
    <div class="error-wrap">

        <!-- Illustration -->
        <div class="error-illustration">
            <svg viewBox="0 0 280 240" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Ground -->
                <ellipse cx="140" cy="228" rx="95" ry="10" fill="#e9ecef"/>

                <!-- Telescope base -->
                <rect x="125" y="190" width="10" height="38" rx="4" fill="#adb5bd"/>
                <rect x="105" y="222" width="50" height="8" rx="4" fill="#adb5bd"/>

                <!-- Telescope tube -->
                <rect x="108" y="120" width="18" height="80" rx="9" transform="rotate(-35 108 120)" fill="#6861CE"/>
                <ellipse cx="93" cy="142" rx="12" ry="9" transform="rotate(-35 93 142)" fill="#4a43b0"/>

                <!-- Stars scattered -->
                <circle cx="52" cy="48" r="3" fill="#1572E8"/>
                <circle cx="228" cy="60" r="2.5" fill="#6861CE"/>
                <circle cx="196" cy="30" r="2" fill="#1572E8"/>
                <circle cx="78" cy="30" r="2" fill="#adb5bd"/>
                <circle cx="240" cy="110" r="3" fill="#dee2e6"/>
                <circle cx="40" cy="100" r="2" fill="#dee2e6"/>

                <!-- Planet with ring (the "lost page" symbol) -->
                <circle cx="175" cy="82" r="38" fill="#eff4ff" stroke="#1572E8" stroke-width="2.5"/>
                <text x="175" y="72" text-anchor="middle" font-size="11" fill="#1572E8" font-family="Public Sans, sans-serif" font-weight="600">Halaman</text>
                <text x="175" y="88" text-anchor="middle" font-size="11" fill="#1572E8" font-family="Public Sans, sans-serif" font-weight="600">tidak</text>
                <text x="175" y="104" text-anchor="middle" font-size="11" fill="#1572E8" font-family="Public Sans, sans-serif" font-weight="600">ditemukan</text>
                <!-- Ring -->
                <ellipse cx="175" cy="82" rx="50" ry="13" stroke="#6861CE" stroke-width="2" fill="none" stroke-dasharray="6 3"/>

                <!-- Question mark floating -->
                <circle cx="68" cy="78" r="22" fill="#fff" stroke="#dee2e6" stroke-width="1.5"/>
                <text x="68" y="85" text-anchor="middle" font-size="22" fill="#1572E8" font-family="Public Sans, sans-serif" font-weight="700">?</text>
            </svg>
        </div>

        <!-- Content -->
        <div class="error-content">
            <span class="error-badge">Error 404</span>
            <div class="error-code">404</div>
            <div class="error-divider"></div>
            <h1 class="error-title">Halaman Tidak Ditemukan</h1>
            <p class="error-desc">
                Sepertinya halaman yang Anda cari sudah tidak ada, dipindahkan, atau
                alamat URL-nya salah ketik. Yuk kembali ke tempat yang aman!
            </p>
            <div class="error-actions">
                <a href="{{ route('dashboard') }}" class="btn-primary-custom">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9L12 2l9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kembali ke Dashboard
                </a>
                <a href="javascript:history.back()" class="btn-secondary-custom">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Halaman Sebelumnya
                </a>
            </div>
        </div>

    </div>
</body>
</html>
