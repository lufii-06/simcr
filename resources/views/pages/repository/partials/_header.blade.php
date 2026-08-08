<div class="row">
    <div class="col-md-12">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-title rounded-circle border border-white bg-primary">
                                    <i class="fas fa-code"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">{{ $repository->name ?? 'Unknown Repository' }}</h4>
                                <div class="text-muted small">Project: {{ $repository->project?->name ?? 'No Project' }}</div>
                                <div class="mt-2">
                                    @if ($repository->is_public ?? false)
                                        <span class="badge badge-primary"><i class="fas fa-globe"></i> Public</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-lock"></i> Private</span>
                                    @endif

                                    @if (($repository->status ?? '') === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 border-start">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">SSH Clone URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm bg-light" id="ssh-url"
                                        value="{{ "git clone ".config('services.repository.root_url') }}:repositories/{{ $repository->name ?? 'unknown' }}.git"
                                        readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-sm" type="button"
                                            onclick="copyToClipboard('ssh-url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">HTTP Clone URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm bg-light" id="http-url"
                                        value="{{ "git clone ".$httpUrl }}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary btn-sm" type="button"
                                            onclick="copyToClipboard('http-url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                @if (!($repository->is_public ?? true) && !($repository->access_token ?? false))
                                    <div class="small text-danger mt-1" style="font-size: 10px;">Token required for
                                        HTTP clone</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
