<div class="tab-pane fade" id="pills-compare" role="tabpanel" aria-labelledby="pills-compare-tab">
    <div class="card border mb-3">
        <div class="card-header bg-light">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-balance-scale me-2 text-primary"></i> Compare Branches / Commits
            </h5>
        </div>
        <div class="card-body">
            <input type="hidden" id="compare-mode" value="branch">
            
            <div class="d-flex justify-content-center mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm px-3" id="btn-mode-branch" onclick="setCompareMode('branch')">
                        <i class="fas fa-code-branch me-1"></i> Compare Branches
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm px-3" id="btn-mode-commit" onclick="setCompareMode('commit')">
                        <i class="fas fa-history me-1"></i> Compare Commits
                    </button>
                </div>
            </div>

            <!-- Branch Mode Inputs -->
            <div class="row align-items-end" id="compare-branch-inputs">
                <div class="col-md-5 mb-2">
                    <label for="compare-base" class="form-label fw-bold small text-muted">Base Branch (Target)</label>
                    <select class="form-select" id="compare-base">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch }}" {{ $branch === $repository->default_branch ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <label for="compare-head" class="form-label fw-bold small text-muted">Compare Branch (Source)</label>
                    <select class="form-select" id="compare-head">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch }}" {{ $branch !== $repository->default_branch ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 d-grid">
                    <button class="btn btn-primary btn-block btn-compare-action">
                        <i class="fas fa-search me-1"></i> Compare
                    </button>
                </div>
            </div>

            <!-- Commit Mode Inputs -->
            <div class="row align-items-end" id="compare-commit-inputs" style="display: none;">
                <div class="col-md-5 mb-2">
                    <label for="compare-base-commit" class="form-label fw-bold small text-muted">Base Commit Hash (Target)</label>
                    <input type="text" class="form-control" id="compare-base-commit" placeholder="e.g. 1a2b3c4">
                </div>
                <div class="col-md-5 mb-2">
                    <label for="compare-head-commit" class="form-label fw-bold small text-muted">Compare Commit Hash (Source)</label>
                    <input type="text" class="form-control" id="compare-head-commit" placeholder="e.g. 5e6f7g8">
                </div>
                <div class="col-md-2 mb-2 d-grid">
                    <button class="btn btn-primary btn-block btn-compare-action">
                        <i class="fas fa-search me-1"></i> Compare
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Results Container -->
    <div id="compare-results" style="display: none;">
        <!-- Alert stats -->
        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-info-circle me-3 fa-lg"></i>
            <div>
                <span id="compare-summary-text" class="fw-bold"></span>
            </div>
        </div>

        <!-- Tabs inside Comparison: Commits and File Changes -->
        <ul class="nav nav-tabs mb-3" id="compareTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="compare-commits-tab" data-bs-toggle="tab" data-bs-target="#compare-commits-pane" type="button" role="tab" style="border: 1px solid #dee2e6; margin-right: 4px;">
                    <i class="fas fa-history me-1"></i> Commits (<span id="compare-commits-count">0</span>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="compare-files-tab" data-bs-toggle="tab" data-bs-target="#compare-files-pane" type="button" role="tab" style="border: 1px solid #dee2e6;">
                    <i class="fas fa-file-signature me-1"></i> File Changes (<span id="compare-files-count">0</span>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="compareTabContent">
            <!-- Commits Pane -->
            <div class="tab-pane fade show active" id="compare-commits-pane" role="tabpanel">
                <div class="table-responsive bg-white border rounded">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hash</th>
                                <th>Message</th>
                                <th>Author</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="compare-commits-list">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- File Changes Pane -->
            <div class="tab-pane fade" id="compare-files-pane" role="tabpanel">
                <div id="compare-files-diffs">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="text-center py-5" id="compare-loading" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Comparing branches...</p>
    </div>
</div>
