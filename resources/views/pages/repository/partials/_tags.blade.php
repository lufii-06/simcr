<div class="tab-pane fade" id="pills-tags" role="tabpanel" aria-labelledby="pills-tags-tab">
    <div class="list-group list-group-messages list-group-flush">
        @forelse ($tags as $tag)
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tag text-warning me-3"></i>
                    <span class="fw-bold">{{ $tag }}</span>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">No tags found.</div>
        @endforelse
    </div>
</div>
