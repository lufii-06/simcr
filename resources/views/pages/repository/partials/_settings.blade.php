<div class="tab-pane fade" id="pills-settings" role="tabpanel" aria-labelledby="pills-settings-tab">
    <div class="p-3">
        <h5 class="fw-bold mb-4">Repository Settings</h5>

        <div class="row">
            <div class="col-md-6 border-end">
                <div class="mb-4">
                    <h6 class="fw-bold">Visibility</h6>
                    <p class="text-muted small">Change whether this repository is public or private.
                    </p>
                    <div class="d-flex align-items-center mt-2">
                        <span class="me-3">Current: <b>{{ $repository->is_public ? 'Public' : 'Private' }}</b></span>
                        <form action="{{ route('repository.toggle-visibility', $repository) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm btn-outline-{{ $repository->is_public ? 'warning' : 'primary' }}">
                                Switch to {{ $repository->is_public ? 'Private' : 'Public' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold">Repository Status</h6>
                    <p class="text-muted small">Activate or Deactivate this repository.
                    </p>
                    <div class="d-flex align-items-center mt-2">
                        <span class="me-3">Current: <b>{{ ucfirst($repository->status) }}</b></span>
                        <form action="{{ route('repository.toggle-status', $repository) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm btn-outline-{{ $repository->status === 'active' ? 'danger' : 'success' }}">
                                Mark as {{ $repository->status === 'active' ? 'Inactive' : 'Active' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ps-md-4">
                <h6 class="fw-bold">Access Token</h6>
                <p class="text-muted small">Required for cloning private repositories via HTTP.
                </p>
                @if ($repository->access_token)
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm" id="token-value"
                            value="{{ $repository->access_token }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="copyToClipboard('token-value')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-danger small">No token generated yet.</p>
                @endif
                <form action="{{ route('repository.generate-token', $repository) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark mt-2">
                        {{ $repository->access_token ? 'Reset Token' : 'Generate Token' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>