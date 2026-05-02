@extends('dashboard')

@section('title', 'Project Analytics: ' . $project->name)

@section('content')
    <div class="page-header">
        <h4 class="page-title">Project Analytics</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="{{ route('project.index', ['view' => 'analytics']) }}">Analytics</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>{{ $project->name }}</a></li>
        </ul>
    </div>

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
                    <ol class="activity-feed">
                        @php
                            $allLogs = $teamStats->pluck('logs')->flatten()->sortByDesc('created_at')->take(15);
                        @endphp
                        @forelse($allLogs as $log)
                            <li class="feed-item feed-item-{{ $log->action == 'status_changed' ? 'warning' : ($log->action == 'created' ? 'primary' : 'success') }}">
                                <time class="date" datetime="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</time>
                                <span class="text">
                                    <strong>{{ $log->user->name }}</strong>: {{ $log->details }}
                                </span>
                            </li>
                        @empty
                            <li class="text-muted">No activities recorded yet.</li>
                        @endforelse
                    </ol>
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
