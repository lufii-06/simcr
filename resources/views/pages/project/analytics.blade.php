@extends('dashboard')

@section('title', 'Project Analytics: ' . $project->name)

@section('content')

    <div class="row">
        <!-- Team Overview -->
        @foreach($teamStats as $stat)
            <div class="col-md-4">
                <div class="card card-profile">
                    <div class="card-header" style="background-image: url('/assets/img/blogpost.jpg')">
                        <div class="profile-picture">
                            <div class="avatar avatar-xl">
                                <img src="{{ $stat['user']->avatar_url }}" alt="..." class="avatar-img rounded-circle">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="user-profile text-center">
                            <div class="name">{{ $stat['user']->name }}</div>
                            <div class="job">{{ $stat['role']->name }}</div>
                            <div class="desc">Team Member</div>
                            
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Task Completion</span>
                                    <span class="text-muted fw-bold">{{ round($stat['progress']) }}%</span>
                                </div>
                                <div class="progress mb-3" style="height: 7px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stat['progress'] }}%"></div>
                                </div>
                                <div class="row text-center mt-3">
                                    <div class="col">
                                        <h5 class="fw-bold mb-0">{{ $stat['total_tasks'] }}</h5>
                                        <small class="text-muted">Assigned</small>
                                    </div>
                                    <div class="col">
                                        <h5 class="fw-bold mb-0 text-success">{{ $stat['completed_tasks'] }}</h5>
                                        <small class="text-muted">Finished</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-block btn-view-history" data-user-id="{{ $stat['user']->id }}" data-user-name="{{ $stat['user']->name }}">
                            View Activity History
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Project Activity Timeline (Recent)</h4>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        @forelse($projectLogs as $log)
                            @php
                                $icon = 'fas fa-plus';
                                $color = 'primary';
                                if ($log->action == 'status_changed') {
                                    $icon = 'fas fa-exchange-alt';
                                    $color = 'warning';
                                } elseif ($log->action == 'checklist_toggled') {
                                    $icon = 'fas fa-check-circle';
                                    $color = 'success';
                                }
                                $inverted = $loop->even ? 'timeline-inverted' : '';
                            @endphp
                            <li class="{{ $inverted }}">
                                <div class="timeline-badge {{ $color }}">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title fw-bold text-{{ $color }}">{{ $log->user->name }}</h4>
                                        <p>
                                            <small class="text-muted"><i class="far fa-clock"></i>
                                                {{ $log->created_at->translatedFormat('d M Y, H:i') }} ({{ $log->created_at->diffForHumans() }})</small>
                                        </p>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-1">{{ $log->details }}</p>
                                        <span class="badge badge-count bg-light text-muted border">
                                            <i class="fas fa-tasks mr-1"></i> {{ $log->task->code ?? 'T-XXX' }}: {{ Str::limit($log->task->title ?? '', 40) }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada aktivitas yang tercatat untuk proyek ini.</p>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">User Activity History</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="history-content"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-view-history').on('click', function() {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');
            const projectId = "{{ $project->id }}";
            
            $('#historyModalLabel').text('Activity History: ' + userName);
            $('#history-content').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $('#historyModal').modal('show');

            $.get(`{{ route('task.log') }}?project_id=${projectId}&user_id=${userId}`, function(html) {
                // Since Task Log is a full page, we'll just extract the table part or use a JSON endpoint
                // For simplicity now, let's just use the existing log data from the teamStats if we pass it correctly
                // Or redirect to the log page with filters
                window.location.href = `{{ route('task.log') }}?project_id=${projectId}&user_id=${userId}`;
            });
        });
    });
</script>
@endpush
