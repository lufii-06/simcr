<div class="tab-pane fade" id="pills-members" role="tabpanel" aria-labelledby="pills-members-tab">
    <div class="list-group list-group-flush">
        <div class="list-group-item bg-light fw-bold">Project Owner</div>
        <div class="list-group-item">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span
                        class="avatar-title rounded-circle border border-white bg-info">{{ substr($repository->project?->owner?->name ?? 'O', 0, 1) }}</span>
                </div>
                <div>
                    <div class="fw-bold">{{ $repository->project?->owner?->name ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $repository->project?->owner?->email ?? '' }}
                    </div>
                </div>
                <span class="badge badge-info ms-auto">Owner</span>
            </div>
        </div>

        <div class="list-group-item bg-light fw-bold mt-3">Developers</div>
        @forelse (($repository->project?->developers ?? []) as $dev)
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                        <span
                            class="avatar-title rounded-circle border border-white bg-secondary">{{ substr($dev->user?->name ?? 'D', 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $dev->user?->name ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $dev->user?->email ?? '' }}</div>
                    </div>
                    <span class="badge badge-secondary ms-auto">{{ $dev->role?->name ?? 'Member' }}</span>
                </div>
            </div>
        @empty
            <div class="list-group-item text-center py-3 text-muted">No developers assigned.</div>
        @endforelse
    </div>
</div>
