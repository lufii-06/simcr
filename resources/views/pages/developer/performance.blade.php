@extends('dashboard')

@section('title', 'Developer Performance – ' . ($user->name ?? 'N/A'))

@section('content')
<style>
    .metric-card {
        border-radius: 12px;
        padding: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .metric-card .metric-icon {
        position: absolute;
        right: 16px;
        top: 16px;
        font-size: 3rem;
        opacity: 0.2;
    }
    .metric-card .metric-value {
        font-size: 2.4rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .metric-card .metric-label {
        font-size: 0.85rem;
        opacity: 0.85;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .progress-bar-custom { height: 10px; border-radius: 5px; }
    .status-badge-todo      { background: #6c757d; color:#fff; }
    .status-badge-progress  { background: #f0a500; color:#fff; }
    .status-badge-done      { background: #28a745; color:#fff; }
    .nav-tabs .nav-link.active { font-weight: 600; }
    .project-block { border-left: 4px solid #4c86f9; padding-left: 12px; }
</style>

<div class="row mb-4">
    {{-- Developer Profile Card --}}
    <div class="col-md-3">
        <div class="card h-100 border shadow-sm">
            <div class="card-body text-center d-flex flex-column align-items-center justify-content-center py-4">
                <img src="{{ $user->getAvatarUrl() }}"
                     class="rounded-circle border border-3 mb-3"
                     style="width:100px;height:100px;object-fit:cover;">
                <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
                <span class="badge badge-primary mb-2">{{ $developer->specialization?->name ?? 'General' }}</span>
                <p class="text-muted small mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Joined {{ $developer->created_at->format('d M Y') }}
                </p>
                @if ($developer->portfolio_url)
                    <a href="{{ $developer->portfolio_url }}" target="_blank" class="btn btn-outline-info btn-sm btn-round mt-3">
                        <i class="fas fa-link me-1"></i> Portfolio
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="col-md-9">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card" style="background: linear-gradient(135deg,#4c86f9,#2563eb);">
                    <i class="fas fa-folder-open metric-icon"></i>
                    <div class="metric-value">{{ count($projectStats) }}</div>
                    <div class="metric-label text-white">Projects</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card" style="background: linear-gradient(135deg,#f0a500,#d97706);">
                    <i class="fas fa-tasks metric-icon"></i>
                    <div class="metric-value">{{ $totalTasksAll }}</div>
                    <div class="metric-label text-white">Total Tasks</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card" style="background: linear-gradient(135deg,#28a745,#16a34a);">
                    <i class="fas fa-check-circle metric-icon"></i>
                    <div class="metric-value">{{ $totalDoneAll }}</div>
                    <div class="metric-label text-white">Tasks Done</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card" style="background: linear-gradient(135deg,#6f42c1,#7c3aed);">
                    <i class="fas fa-percentage metric-icon"></i>
                    <div class="metric-value text-white">{{ $globalCompletionRate }}%</div>
                    <div class="metric-label text-white">Completion Rate</div>
                </div>
            </div>

            {{-- Global Progress Bar --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-dark">Overall Task Progress</span>
                            <span class="badge" style="background:#6f42c1;color:#fff;font-size:0.85rem;">
                                {{ $totalDoneAll }} / {{ $totalTasksAll }} done
                            </span>
                        </div>
                        <div class="progress progress-bar-custom">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: {{ $globalCompletionRate }}%"
                                 aria-valuenow="{{ $globalCompletionRate }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">0%</small>
                            <small class="text-muted">{{ $globalCompletionRate }}% completed</small>
                            <small class="text-muted">100%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (count($projectStats) === 0)
    <div class="alert alert-info py-4 text-center">
        <i class="fas fa-info-circle me-2"></i>
        Developer ini belum ditugaskan ke proyek manapun.
    </div>
@else

{{-- Main Tab: By Task | By Repository --}}
<ul class="nav nav-tabs mb-4" id="performanceTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tab-task-btn"
                data-bs-toggle="tab" data-bs-target="#tab-task" type="button" role="tab">
            <i class="fas fa-tasks me-1"></i> Kontribusi by Task
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-repo-btn"
                data-bs-toggle="tab" data-bs-target="#tab-repo" type="button" role="tab">
            <i class="fas fa-code-branch me-1"></i> Kontribusi by Repository
        </button>
    </li>
</ul>

<div class="tab-content" id="performanceTabContent">

    {{-- ===================== TAB 1: BY TASK ===================== --}}
    <div class="tab-pane fade show active" id="tab-task" role="tabpanel">
        <div class="row mb-4">
            {{-- Per-project task summary table --}}
            <div class="col-12">
                <div class="card border shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Ringkasan Task per Project
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project</th>
                                    <th class="text-center" style="width:120px;">Role</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Done</th>
                                    <th class="text-center">Pending</th>
                                    <th style="width:200px;">Progress</th>
                                    <th class="text-center">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projectStats as $ps)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $ps['project']->name }}</div>
                                            <div class="text-muted small">{{ $ps['project']->code }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $ps['role'] }}</span>
                                        </td>
                                        <td class="text-center fw-semibold">{{ $ps['total_tasks'] }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success">{{ $ps['done_tasks'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($ps['pending_tasks'] > 0)
                                                <span class="badge badge-warning text-dark">{{ $ps['pending_tasks'] }}</span>
                                            @else
                                                <span class="badge badge-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress progress-bar-custom">
                                                <div class="progress-bar
                                                    {{ $ps['completion_rate'] === 100 ? 'bg-success' : ($ps['completion_rate'] >= 50 ? 'bg-info' : 'bg-warning') }}"
                                                     style="width:{{ $ps['completion_rate'] }}%">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold
                                            {{ $ps['completion_rate'] === 100 ? 'text-success' : ($ps['completion_rate'] >= 50 ? 'text-info' : 'text-warning') }}">
                                            {{ $ps['completion_rate'] }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Detail per Project (accordion) --}}
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-list-ul me-2 text-primary"></i>
                    Detail Task per Project
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="taskAccordion">
                    @foreach ($projectStats as $idx => $ps)
                        <div class="accordion-item border mb-2 rounded">
                            <h2 class="accordion-header" id="taskHead{{ $idx }}">
                                <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }} fw-semibold"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#taskBody{{ $idx }}"
                                        aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                                    <span class="project-block me-3">
                                        {{ $ps['project']->name }}
                                        <small class="text-muted ms-2">({{ $ps['done_tasks'] }}/{{ $ps['total_tasks'] }} done)</small>
                                    </span>
                                </button>
                            </h2>
                            <div id="taskBody{{ $idx }}"
                                 class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}"
                                 data-bs-parent="#taskAccordion">
                                <div class="accordion-body p-0">
                                    @if (count($ps['task_list']) === 0)
                                        <p class="text-muted text-center py-3">Tidak ada task yang di-assign ke developer ini di project ini.</p>
                                    @else
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:110px;">Kode</th>
                                                    <th>Judul Task</th>
                                                    <th class="text-center" style="width:130px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ps['task_list'] as $task)
                                                    <tr>
                                                        <td><code class="text-primary">{{ $task['code'] }}</code></td>
                                                        <td>{{ $task['title'] }}</td>
                                                        <td class="text-center">
                                                            @php
                                                                $statusLower = strtolower($task['status']);
                                                                $badgeClass = 'badge-primary';
                                                                if ($statusLower === 'done') {
                                                                    $badgeClass = 'badge-success';
                                                                } elseif (str_contains($statusLower, 'progress')) {
                                                                    $badgeClass = 'badge-warning';
                                                                }
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }}">{{ $task['status'] }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bar Chart: Task done per project --}}
        <div class="card border shadow-sm mt-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                    Grafik Task per Project
                </h5>
            </div>
            <div class="card-body">
                <canvas id="taskChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- ===================== TAB 2: BY REPOSITORY ===================== --}}
    <div class="tab-pane fade" id="tab-repo" role="tabpanel">

        {{-- Commit summary table --}}
        <div class="card border shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-table me-2 text-primary"></i>
                    Ringkasan Commit per Project Repository
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Repository</th>
                            <th class="text-center">Total Commits</th>
                            <th class="text-center">Status Repository</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectStats as $ps)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $ps['project']->name }}</div>
                                    <div class="text-muted small">{{ $ps['project']->code }}</div>
                                </td>
                                <td>
                                    @if ($ps['has_repo'])
                                        <span class="text-dark">{{ $ps['project']->repository->name ?? '-' }}</span>
                                    @else
                                        <span class="text-muted fst-italic">No repository</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($ps['has_repo'])
                                        <span class="fw-bold text-primary fs-5">{{ $ps['commit_count'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($ps['has_repo'])
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">No Repo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-light fw-bold">
                            <td colspan="2">Total Commits (All Projects)</td>
                            <td class="text-center text-primary fs-5">{{ $totalCommitsAll }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bar Chart: Commit per project --}}
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                    Grafik Commit per Project Repository
                </h5>
            </div>
            <div class="card-body">
                @php $hasAnyCommit = collect($projectStats)->sum('commit_count') > 0; @endphp
                @if (!$hasAnyCommit)
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada data commit Git untuk developer ini. Pastikan nama Git author-nya cocok dengan nama akun di SIMCR
                        (<strong>"{{ $user->name }}"</strong>).
                    </div>
                @else
                    <canvas id="commitChart" height="100"></canvas>
                @endif
            </div>
        </div>
    </div>

</div>{{-- /tab-content --}}
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    @php
        $labels   = collect($projectStats)->pluck('project.name')->map(fn($n) => Str::limit($n, 20))->toArray();
        $doneCounts  = collect($projectStats)->pluck('done_tasks')->toArray();
        $pendingCounts = collect($projectStats)->pluck('pending_tasks')->toArray();
        $commitCounts  = collect($projectStats)->pluck('commit_count')->toArray();
    @endphp

    var labels = {!! json_encode($labels) !!};
    var doneCounts  = {!! json_encode($doneCounts) !!};
    var pendingCounts = {!! json_encode($pendingCounts) !!};
    var commitCounts  = {!! json_encode($commitCounts) !!};

    // --- Task Chart ---
    var taskCtx = document.getElementById('taskChart');
    if (taskCtx) {
        new Chart(taskCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Done',
                        data: doneCounts,
                        backgroundColor: 'rgba(40,167,69,0.8)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Pending',
                        data: pendingCounts,
                        backgroundColor: 'rgba(240,165,0,0.8)',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: { stacked: false },
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    // --- Commit Chart ---
    var commitCtx = document.getElementById('commitChart');
    if (commitCtx) {
        new Chart(commitCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Commits',
                    data: commitCounts,
                    backgroundColor: 'rgba(76,134,249,0.85)',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { position: 'top' } }
            }
        });
    }
});
</script>
@endpush
@endsection
