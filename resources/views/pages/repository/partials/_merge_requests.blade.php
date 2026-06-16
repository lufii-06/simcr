<div class="tab-pane fade" id="pills-merges" role="tabpanel" aria-labelledby="pills-merges-tab">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-code-branch me-2 text-primary"></i> Merge Requests
        </h5>
        @if (auth()->user()->role !== 'client')
            <a href="{{ route('repository.merge-requests.create', $repository) }}" class="btn btn-primary btn-sm btn-round">
                <i class="fas fa-plus me-1"></i> New Merge Request
            </a>
        @endif
    </div>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Title</th>
                    <th>Branches</th>
                    <th>Author</th>
                    <th>Created At</th>
                    <th class="text-center" style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mergeRequests as $mr)
                    <tr>
                        <td>
                            <a href="{{ route('repository.merge-requests.show', [$repository->name, $mr->id]) }}" class="fw-bold">
                                #{{ $mr->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('repository.merge-requests.show', [$repository->name, $mr->id]) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $mr->title }}
                            </a>
                            @if ($mr->description)
                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                    {{ $mr->description }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <code class="text-danger bg-light px-1 py-0.5 rounded">{{ $mr->source_branch }}</code>
                            <i class="fas fa-arrow-right mx-2 text-muted text-xs"></i>
                            <code class="text-primary bg-light px-1 py-0.5 rounded">{{ $mr->target_branch }}</code>
                        </td>
                        <td>
                            <span class="text-dark small">{{ $mr->user->name ?? 'System' }}</span>
                        </td>
                        <td class="text-nowrap small text-muted">
                            {{ $mr->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="text-center">
                            @if ($mr->status === 'open')
                                <span class="badge badge-success px-3 py-1">Open</span>
                            @elseif ($mr->status === 'merged')
                                <span class="badge px-3 py-1 text-white" style="background-color: #6f42c1 !important;">Merged</span>
                            @else
                                <span class="badge badge-danger px-3 py-1">Closed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-code-branch fa-3x mb-3 text-muted" style="opacity: 0.5;"></i>
                            <p class="mb-0 fw-semibold">No Merge Requests found.</p>
                            <p class="text-xs mb-0">Create a new merge request to merge changes from developer branches.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
