<div class="card card-profile">
    <div class="card-header" style="background-image: url('{{ asset('assets/img/blogpost.jpg') }}')">
        <div class="profile-picture">
            <div class="avatar avatar-xl">
                <span class="avatar-title rounded-circle border border-white bg-primary"><i
                        class="fas fa-info-circle fa-2x"></i></span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="user-profile text-center">
            <div class="name">Repository Statistics</div>
            <div class="job">Overall health and details</div>
            @if ($repository->project)
                <div class="mt-3">
                    <a href="{{ route('project.analytics', $repository->project) }}" class="btn btn-primary btn-sm btn-round">
                        <i class="fas fa-chart-line mr-1"></i> View Project Analytics
                    </a>
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="row user-stats text-center">
            <div class="col">
                <div class="number">{{ count($branches ?? []) }}</div>
                <div class="title">Branches</div>
            </div>
            <div class="col">
                <div class="number">{{ count($tags ?? []) }}</div>
                <div class="title">Tags</div>
            </div>
            <div class="col">
                <div class="number">{{ count($recentCommits ?? []) }}</div>
                <div class="title">Commits</div>
            </div>
        </div>
    </div>
</div>
