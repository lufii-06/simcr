<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Akses Ditolak | SIMCR</title>
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
            background: #fff3f3;
            color: #f25961;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #fdd;
            margin-bottom: 16px;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            line-height: 1;
            color: #f25961;
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
            background: #f25961;
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
            background: #d94850;
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
            background: #f25961;
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
                <!-- Ground shadow -->
                <ellipse cx="140" cy="228" rx="90" ry="10" fill="#e9ecef"/>

                <!-- Lock body -->
                <rect x="72" y="118" width="136" height="96" rx="14" fill="#fff" stroke="#dee2e6" stroke-width="2"/>
                <rect x="82" y="128" width="116" height="76" rx="10" fill="#fff3f3"/>

                <!-- Lock shackle -->
                <path d="M102 118 V88 Q102 62 140 62 Q178 62 178 88 V118" stroke="#f25961" stroke-width="12" stroke-linecap="round" fill="none"/>

                <!-- Keyhole -->
                <circle cx="140" cy="162" r="14" fill="#f25961"/>
                <rect x="135" y="168" width="10" height="18" rx="5" fill="#f25961"/>

                <!-- Shield badge top-right -->
                <circle cx="196" cy="100" r="22" fill="#f25961"/>
                <text x="196" y="107" text-anchor="middle" font-size="18" fill="#fff" font-family="Public Sans, sans-serif" font-weight="700">!</text>

                <!-- Dots decoration -->
                <circle cx="55" cy="155" r="5" fill="#dee2e6"/>
                <circle cx="44" cy="173" r="3" fill="#dee2e6"/>
                <circle cx="232" cy="178" r="4" fill="#f2596133"/>
                <circle cx="244" cy="160" r="6" fill="#f2596122"/>
            </svg>
        </div>

        <!-- Content -->
        <div class="error-content">
            <span class="error-badge">Error 403</span>
            <div class="error-code">403</div>
            <div class="error-divider"></div>
            <h1 class="error-title">Akses Ditolak</h1>
            <p class="error-desc">
                Maaf, Anda tidak punya izin untuk membuka halaman ini.
                Hubungi administrator jika Anda merasa seharusnya bisa mengaksesnya.
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
