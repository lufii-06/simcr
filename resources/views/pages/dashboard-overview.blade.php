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
                            <p class="text-white op-7">Ringkasan aktivitas proyek Anda untuk hari ini.</p>
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
                                <h4 class="card-title">{{ $totalProjects }}</h4>
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
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Tasks Today</p>
                                <h4 class="card-title">{{ $tasksToday }}</h4>
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
                                <h4 class="card-title">{{ $completedTasks }}</h4>
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
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Pending Tasks</p>
                                <h4 class="card-title">{{ $pendingTasks }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Status Chart -->
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Task Distribution</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="taskStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities / Tasks -->
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Recent Tasks</h4>
                        <a href="{{ route('task.index') }}" class="btn btn-primary btn-round btn-sm ms-auto">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                    <tr>
                                        <td><strong>{{ Str::limit($task->title, 25) }}</strong></td>
                                        <td>{{ $task->project->name }}</td>
                                        <td>{{ $task->assignee->name }}</td>
                                        <td>
                                            @php
                                                $statusName = $task->status->name ?? '';
                                                $badgeClass = 'badge-info';
                                                if ($statusName === 'To Do') {
                                                    $badgeClass = 'badge-primary';
                                                } elseif ($statusName === 'In Progress') {
                                                    $badgeClass = 'badge-warning';
                                                } elseif ($statusName === 'Done') {
                                                    $badgeClass = 'badge-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No recent tasks.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

@push('scripts')
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Task Status Doughnut Chart
        var ctx = document.getElementById('taskStatusChart').getContext('2d');
        var taskStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [
                        @foreach($statusChart as $status)
                            {{ $status->tasks_count }},
                        @endforeach
                    ],
                    backgroundColor: ['#1d7af3', '#f3545d', '#fdaf4b', '#59d05d', '#177dff'],
                }],
                labels: [
                    @foreach($statusChart as $status)
                        '{{ $status->name }}',
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                layout: {
                    padding: {
                        top: 20
                    }
                }
            }
        });
    });
</script>
@endpush