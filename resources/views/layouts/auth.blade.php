<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title') - SIMCR</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" href="{{ asset('images/logo-icon.png') }}" type="image/x-icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            custom: {
                "families": ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
                    "simple-line-icons"
                ],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"]
            },
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(79,70,229,0.12), 0 2px 6px rgba(0,0,0,0.06);
            --shadow-lg: 0 20px 60px rgba(79,70,229,0.18), 0 8px 24px rgba(0,0,0,0.08);
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #0f0c29;
            display: block;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===================== AUTH WRAPPER ===================== */
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ===================== LEFT PANEL ===================== */
        .auth-left {
            flex: 0 0 42%;
            background: linear-gradient(145deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            overflow: hidden;
        }

        /* Decorative orbs */
        .auth-left::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79,70,229,0.35) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6,182,212,0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-orb-mid {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(129,140,248,0.08) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Grid pattern overlay */
        .auth-grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        .auth-left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 380px;
        }

        .auth-brand {
            margin-bottom: 2.5rem;
        }

        .auth-brand img {
            height: 52px;
            filter: brightness(0) invert(1);
            opacity: 0.95;
            margin-bottom: 1rem;
        }

        .auth-brand-tagline {
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
        }

        .auth-headline {
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 1rem;
        }

        .auth-headline span {
            background: linear-gradient(135deg, #818cf8, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-subtext {
            font-size: 0.92rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            margin-bottom: 2.5rem;
        }

        /* Feature badges */
        .auth-features {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .auth-feature-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 0.7rem 1rem;
            backdrop-filter: blur(10px);
            text-align: left;
            transition: background 0.3s;
        }

        .auth-feature-item:hover {
            background: rgba(255,255,255,0.1);
        }

        .auth-feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .auth-feature-icon.icon-purple { background: rgba(129,140,248,0.25); color: #818cf8; }
        .auth-feature-icon.icon-cyan   { background: rgba(6,182,212,0.25);   color: #06b6d4; }
        .auth-feature-icon.icon-green  { background: rgba(16,185,129,0.25);  color: #10b981; }

        .auth-feature-label {
            font-size: 0.83rem;
            font-weight: 500;
            color: rgba(255,255,255,0.8);
        }

        /* ===================== RIGHT PANEL ===================== */
        .auth-right {
            flex: 1;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            position: relative;
            overflow: hidden;  /* layar utama tidak scroll */
        }

        .auth-right::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #818cf8, #06b6d4);
            z-index: 10;
        }

        /* Login: center normal */
        .auth-form-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 3rem 2rem;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Register: fill full panel, internal scroll */
        .auth-form-wrapper.register-wrapper {
            max-width: 100%;
            height: 100%;
            padding: 2.5rem 3.5rem;
            overflow-y: auto;
            align-self: stretch;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-form-header {
            margin-bottom: 2rem;
        }

        .auth-form-header .form-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 0.4rem;
        }

        .auth-form-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 0.4rem;
            line-height: 1.2;
        }

        .auth-form-header p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* ===================== FORM ELEMENTS ===================== */
        .auth-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-section-title i {
            color: var(--primary);
        }

        .auth-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .auth-form label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.4rem;
        }

        .auth-form label.required::after {
            content: ' *';
            color: var(--danger);
        }

        .auth-form .form-control {
            width: 100%;
            height: 50px;
            padding: 0 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            background: var(--surface);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        .auth-form textarea.form-control {
            height: auto;
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
            resize: none;
        }

        .auth-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3.5px rgba(79,70,229,0.12);
            background: #fefefe;
        }

        .auth-form .form-control::placeholder {
            color: #a0aec0;
            font-size: 0.84rem;
        }

        .auth-form .form-control.is-invalid,
        .auth-form .has-error .form-control {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        .auth-form .form-group {
            margin-bottom: 1.1rem;
        }

        .auth-form .text-danger {
            font-size: 0.76rem;
            margin-top: 0.25rem;
            display: block;
            color: var(--danger) !important;
        }

        /* Password toggle */
        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .form-control {
            padding-right: 2.8rem;
        }

        .input-toggle-btn {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #94a3b8;
            font-size: 0.85rem;
            line-height: 1;
            transition: color 0.2s;
        }

        .input-toggle-btn:hover { color: var(--primary); }

        /* Remember me */
        .auth-remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .auth-remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .auth-remember label {
            font-size: 0.83rem;
            color: var(--text-secondary);
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        /* Submit button */
        .btn-auth-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-size: 0.925rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            box-shadow: 0 4px 16px rgba(79,70,229,0.35);
            position: relative;
            overflow: hidden;
        }

        .btn-auth-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79,70,229,0.45);
        }

        .btn-auth-submit:hover::after { opacity: 1; }
        .btn-auth-submit:active { transform: translateY(0); }

        /* Footer link */
        .auth-alt-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .auth-alt-link a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .auth-alt-link a:hover { opacity: 0.75; text-decoration: underline; }

        /* Divider for register */
        .auth-col-divider {
            width: 1px;
            background: var(--border);
            margin: 0 1.5rem;
            align-self: stretch;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 991px) {
            .auth-left { display: none; }

            .auth-right {
                overflow-y: auto;
                justify-content: flex-start;
            }

            .auth-right::before { height: 3px; }

            .auth-form-wrapper {
                padding: 2.5rem 1.5rem;
            }

            .auth-form-wrapper.register-wrapper {
                height: auto;
                padding: 2rem 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .auth-form-header h2 { font-size: 1.4rem; }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        {{-- Left Branding Panel --}}
        <div class="auth-left">
            <div class="auth-grid-overlay"></div>
            <div class="auth-orb-mid"></div>
            <div class="auth-left-content">
                <div class="auth-brand">
                    <img src="{{ asset('images/logo-black.png') }}" alt="SIMCR Logo">
                    <div class="auth-brand-tagline">Management System</div>
                </div>
                <h1 class="auth-headline">
                    Manage Your <span>Contract &amp; Requests</span> Effortlessly
                </h1>
                <p class="auth-subtext">
                    One platform to handle all your client contracts, requests, and service workflows — fast, secure, and reliable.
                </p>
                <div class="auth-features">
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon icon-purple"><i class="fas fa-file-contract"></i></div>
                        <span class="auth-feature-label">Contract Management &amp; Tracking</span>
                    </div>
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon icon-cyan"><i class="fas fa-tasks"></i></div>
                        <span class="auth-feature-label">Service Request Workflow</span>
                    </div>
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon icon-green"><i class="fas fa-shield-alt"></i></div>
                        <span class="auth-feature-label">Secure Role-Based Access</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Form Panel --}}
        <div class="auth-right">
            @yield('content')
        </div>
    </div>

    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        @if (Session::has('success'))
            $.notify({
                icon: 'fa fa-check',
                title: 'Success',
                message: "{{ Session::get('success') }}",
            }, {
                type: 'success',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 1000,
            });
        @endif
        @if (Session::has('error'))
            $.notify({
                icon: 'fa fa-times',
                title: 'Error',
                message: "{{ Session::get('error') }}",
            }, {
                type: 'danger',
                placement: {
                    from: "top",
                    align: "right"
                },
                time: 1000,
            });
        @endif
    </script>
    @stack('scripts')
</body>

</html>
