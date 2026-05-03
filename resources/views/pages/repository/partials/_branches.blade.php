<div class="tab-pane fade" id="pills-branches" role="tabpanel" aria-labelledby="pills-branches-tab">
    <div class="list-group list-group-messages list-group-flush">
        @forelse ($branches as $branch)
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <i class="fas fa-code-branch text-info me-3"></i>
                    <span class="fw-bold">{{ $branch }}</span>
                    @if ($branch === $repository->default_branch)
                        <span class="badge badge-primary badge-xs ms-2">Default</span>
                    @endif
                    <a href="?branch={{ $branch }}" class="btn btn-xs btn-link ms-auto">View
                        Commits</a>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">No branches found.</div>
        @endforelse
    </div>
</div>
