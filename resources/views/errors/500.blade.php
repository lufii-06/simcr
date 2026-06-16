<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 – Kesalahan Server | SIMCR</title>
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
            background: #fff8ee;
            color: #f3a435;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #fde8bb;
            margin-bottom: 16px;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            line-height: 1;
            color: #f3a435;
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
            background: #f3a435;
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
            background: #d68e1e;
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
            background: #f3a435;
            border-radius: 2px;
            margin-bottom: 20px;
        }

        /* Subtle pulse on gear -->  */
        .gear-spin {
            transform-origin: 145px 120px;
            animation: spin 6s linear infinite;
        }
        .gear-spin-reverse {
            transform-origin: 185px 155px;
            animation: spin 8s linear infinite reverse;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
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
                <!-- Ground shadow -->
                <ellipse cx="140" cy="228" rx="90" ry="10" fill="#e9ecef"/>

                <!-- Computer monitor -->
                <rect x="60" y="70" width="160" height="110" rx="10" fill="#fff" stroke="#dee2e6" stroke-width="2"/>
                <rect x="66" y="76" width="148" height="98" rx="6" fill="#fff8ee"/>
                <!-- Screen "broken" lines -->
                <line x1="140" y1="76" x2="90" y2="174" stroke="#f3a43566" stroke-width="2"/>
                <line x1="140" y1="76" x2="160" y2="174" stroke="#f3a43544" stroke-width="1.5"/>
                <!-- Warning icon on screen -->
                <circle cx="140" cy="122" r="28" fill="#fff" stroke="#f3a435" stroke-width="2.5"/>
                <text x="140" y="115" text-anchor="middle" font-size="22" fill="#f3a435" font-family="Public Sans,sans-serif" font-weight="700">!</text>
                <rect x="137" y="128" width="6" height="5" rx="2" fill="#f3a435"/>

                <!-- Monitor stand -->
                <rect x="127" y="180" width="26" height="16" rx="3" fill="#adb5bd"/>
                <rect x="108" y="194" width="64" height="8" rx="4" fill="#adb5bd"/>

                <!-- Small gear – spinning -->
                <g class="gear-spin">
                    <circle cx="145" cy="120" r="11" fill="none" stroke="#f3a435" stroke-width="3"/>
                    <rect x="141" y="104" width="8" height="7" rx="2" fill="#f3a435"/>
                    <rect x="141" y="129" width="8" height="7" rx="2" fill="#f3a435"/>
                    <rect x="127" y="116" width="7" height="8" rx="2" fill="#f3a435"/>
                    <rect x="156" y="116" width="7" height="8" rx="2" fill="#f3a435"/>
                </g>

                <!-- Spark dots -->
                <circle cx="60" cy="78" r="4" fill="#f3a43588"/>
                <circle cx="52" cy="92" r="2.5" fill="#f3a43555"/>
                <circle cx="224" cy="82" r="3" fill="#f3a43566"/>
                <circle cx="235" cy="96" r="2" fill="#f3a43544"/>
            </svg>
        </div>

        <!-- Content -->
        <div class="error-content">
            <span class="error-badge">Error 500</span>
            <div class="error-code">500</div>
            <div class="error-divider"></div>
            <h1 class="error-title">Kesalahan Server</h1>
            <p class="error-desc">
                Terjadi kesalahan pada server kami. Tim teknis sudah diberitahu dan sedang
                memperbaikinya. Silakan coba beberapa saat lagi.
            </p>
            <div class="error-actions">
                <a href="{{ route('dashboard') }}" class="btn-primary-custom">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9L12 2l9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kembali ke Dashboard
                </a>
                <a href="javascript:location.reload()" class="btn-secondary-custom">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                    Coba Lagi
                </a>
            </div>
        </div>

    </div>
</body>
</html>
