<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Public Sans', sans-serif;
            background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fdf5f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #2a2f43;
            overflow-x: hidden;
        }
        .error-container {
            text-align: center;
            padding: 40px 30px;
            max-width: 550px;
            width: 100%;
        }
        .error-emoji {
            font-size: 5rem;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
            display: inline-block;
        }
        .error-code {
            font-size: 9rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f25961 0%, #f3bb45 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -2px;
            filter: drop-shadow(0px 10px 20px rgba(242, 89, 97, 0.15));
        }
        .error-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1a2035;
        }
        .error-desc {
            font-size: 1rem;
            color: #727c8e;
            margin-bottom: 35px;
            line-height: 1.6;
        }
        .btn-dashboard {
            background: linear-gradient(135deg, #f25961 0%, #f3bb45 100%);
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 35px;
            border-radius: 50px;
            border: none;
            box-shadow: 0 10px 25px rgba(242, 89, 97, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(242, 89, 97, 0.45);
        }
        .btn-dashboard:active {
            transform: translateY(-1px);
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .circle-bg {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(242, 89, 97, 0.05) 0%, rgba(243, 187, 69, 0.05) 100%);
            z-index: -1;
        }
        .circle-1 {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
        }
        .circle-2 {
            width: 400px;
            height: 400px;
            bottom: -100px;
            right: -100px;
        }
    </style>
</head>
<body>
    <div class="circle-bg circle-1"></div>
    <div class="circle-bg circle-2"></div>
    
    <div class="error-container">
        <div class="error-emoji">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Akses Ditolak</h1>
        <p class="error-desc">Mohon maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-dashboard">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
