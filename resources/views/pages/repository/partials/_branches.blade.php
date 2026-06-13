<div class="tab-pane fade" id="pills-branches" role="tabpanel" aria-labelledby="pills-branches-tab">
    @if (auth()->user()->role !== 'client')
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-info text-white btn-sm btn-round me-2" data-bs-toggle="modal" data-bs-target="#mergeBranchModal">
                <i class="fas fa-code-branch me-1"></i> Merge / Rebase
            </button>
            <button type="button" class="btn btn-primary btn-sm btn-round" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                <i class="fas fa-plus me-1"></i> Create Branch
            </button>
        </div>
    @endif
    <div class="list-group list-group-messages list-group-flush">
        @forelse ($branches as $branch)
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <i class="fas fa-code-branch text-info me-3"></i>
                    <span class="fw-bold">{{ $branch }}</span>
                    @if ($branch === $repository->default_branch)
                        <span class="badge badge-primary badge-xs ms-2">Default</span>
                    @endif
                    <a href="?branch={{ $branch }}" class="btn btn-xs btn-link ms-auto">View Files</a>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">No branches found.</div>
        @endforelse
    </div>
</div>
