@extends('dashboard')

@section('title', 'Dashboard Overview')

@section('content')
    <div class="row">
        <!-- Welcome Header -->
        <div class="col-md-12">
            <div class="card card-round card-bg-gradient-purple">
                <div class="card-body pb-0">
                    <div class="d-flex align-items-center gap-3 p-1">
                        <div class="avatar avatar-xl">
                            <img src="{{ auth()->user()->getAvatarUrl() }}" alt="..." class="avatar-img rounded-circle border border-white shadow">
                        </div>
                        <div class="flex-1">
                            <h2 class="text-white fw-bold mb-1">Selamat Datang Kembali, {{ auth()->user()->name }}!</h2>
                            <p class="text-white op-7">Senang melihat Anda lagi. Berikut adalah ringkasan aktivitas proyek Anda hari ini.</p>
                        </div>
                        <div class="ml-auto text-right">
                            <span class="badge badge-secondary px-3 py-2">
                                <i class="fas fa-user-shield mr-2"></i> Role: {{ strtoupper(auth()->user()->role) }}
                            </span>
                            <div class="text-white mt-2">
                                <i class="far fa-calendar-alt mr-1"></i> {{ now()->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt--2">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Active Projects</p>
                                <h4 class="card-title">12</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Clients</p>
                                <h4 class="card-title">25</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Completed Tasks</p>
                                <h4 class="card-title">150</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Pending Reviews</p>
                                <h4 class="card-title">8</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Informasi Sistem</div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-left-info shadow" role="alert">
                        <strong>Tips:</strong> Anda dapat mengakses pengaturan profil dan ganti foto melalui menu di pojok kanan atas atau melalui Sidebar "My Profile".
                    </div>
                    <p>Halaman dashboard ini sedang dalam tahap pengembangan. Segera akan hadir grafik statistik real-time untuk pemantauan proyek yang lebih detail.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-bg-gradient-purple {
            background: linear-gradient(-45deg, #5856d6, #6f42c1) !important;
            color: white;
            box-shadow: 0 10px 20px rgba(88, 86, 214, 0.2);
        }
        .mt--2 {
            margin-top: -20px !important;
        }
    </style>
@endsection