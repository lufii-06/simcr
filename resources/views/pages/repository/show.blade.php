@extends('dashboard')

@section('title', 'Repository Detail')

@section('content')
    <style>
        .bg-success-light { background-color: #e6ffec !important; }
        .text-success-dark { color: #22863a !important; }
        .bg-danger-light { background-color: #ffeef0 !important; }
        .text-danger-dark { color: #cb2431 !important; }
        .bg-info-light { background-color: #f1f8ff !important; }
        .text-info-dark { color: #032f62 !important; }
        .text-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; }
    </style>

    @if ($repository->status === 'inactive')
        <div class="alert alert-warning">
            <i class="fas fa-pause-circle"></i> This repository is currently <b>Inactive</b>. Operations might be
            restricted.
        </div>
    @endif

    @if ($error)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ $error }}
        </div>
    @endif

    {{-- Header: Repo Info & Clone URLs --}}
    @include('pages.repository.partials._header')

    <div class="row">
        <div class="col-md-4">
            {{-- Stats Cards: Branches, Tags, Commits Count --}}
            @include('pages.repository.partials._stats_cards')

            <!-- Download Archive Card -->
            <div class="card card-round mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-download me-1 text-success"></i> Download Source Code
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download the entire repository source code for the currently selected branch (<b>{{ $selectedBranch }}</b>).</p>
                    <a href="{{ route('repository.download-archive', [$repository, 'branch' => $selectedBranch, 'format' => 'zip']) }}" class="btn btn-success btn-block text-white">
                        <i class="fas fa-file-archive me-2"></i> Download as .ZIP
                    </a>
                    <a href="{{ route('repository.download-archive', [$repository, 'branch' => $selectedBranch, 'format' => 'tar.gz']) }}" class="btn btn-outline-success btn-block text-success">
                        <i class="fas fa-file-alt me-2"></i> Download as .TAR.GZ
                    </a>
                </div>
            </div>

            <!-- Recent Activity Timeline Card -->
            <div class="card card-round mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-history me-1 text-primary"></i> Recent Activity
                    </div>
                </div>
                <div class="card-body">
                    <div class="feed-timeline">
                        @forelse (array_slice($recentCommits, 0, 4) as $commit)
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0; margin-right: 12px;">
                                    <i class="fas fa-code-branch fa-xs"></i>
                                </div>
                                <div class="feed-content flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex align-items-center mb-1 justify-content-between">
                                        <span class="text-xs fw-bold text-primary" style="font-weight: 700; font-size: 0.75rem;">{{ $commit['hash'] }}</span>
                                        <small class="text-muted text-xs" style="font-size: 0.75rem;">{{ $commit['date'] }}</small>
                                    </div>
                                    <p class="mb-0 text-dark text-wrap-break" style="font-size: 0.85rem; line-height: 1.3; overflow-wrap: break-word; word-break: break-word;">
                                        {{ $commit['message'] }}
                                    </p>
                                    <small class="text-muted text-xs d-block mt-1" style="font-size: 0.7rem;"><i class="far fa-user me-1"></i> {{ $commit['author'] }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No recent commits found</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title me-3">Repository Explorer</h4>
                            <select class="form-select form-select-sm me-2" style="width: auto;"
                                onchange="window.location.href='?branch=' + this.value">
                                @forelse ($branches as $branch)
                                    <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }}>
                                        {{ $branch }}
                                    </option>
                                @empty
                                    <option disabled selected>No branches found</option>
                                @endforelse
                            </select>
                            <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()" title="Refresh Explorer">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-clean btn-lg w-auto px-2" type="button"
                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="showTab('pills-insights')">
                                    <i class="fas fa-chart-pie me-2 text-info"></i> Insights & Analytics
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="showTab('pills-tags')">
                                    <i class="fas fa-tags me-2 text-warning"></i> Tags / Releases
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="showTab('pills-members')">
                                    <i class="fas fa-users me-2"></i> Members
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="showTab('pills-guide')">
                                    <i class="fas fa-book me-2"></i> Clone Guide
                                </a>
                                @if (auth()->id() == ($repository->project->user_id ?? 0) || auth()->user()->role == 'admin')
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="showTab('pills-settings')">
                                        <i class="fas fa-cog me-2"></i> Settings
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd" id="pills-tab-without-border" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" id="pills-branches-tab" data-bs-toggle="pill" onclick="showTab('pills-branches')" href="#pills-branches" role="tab" aria-controls="pills-branches" aria-selected="false">
                                <i class="fas fa-code-branch me-1"></i> Branches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-files-tab" data-bs-toggle="pill" href="#pills-files" onclick="showTab('pills-files')"
                                role="tab" aria-controls="pills-files" aria-selected="true">
                                <i class="fas fa-folder-open me-1"></i> Files
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-commits-tab" data-bs-toggle="pill" onclick="showTab('pills-commits')"href="#pills-commits"
                                role="tab" aria-controls="pills-commits" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Commits
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-network-tab" data-bs-toggle="pill" onclick="showTab('pills-network')"href="#pills-network"
                                role="tab" aria-controls="pills-network" aria-selected="false">
                                <i class="fas fa-project-diagram me-1"></i> Network
                            </a>
                        </li>
                        {{-- Hidden Triggers for Kebab Menu --}}
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-insights-tab" data-bs-toggle="pill" onclick="showTab('pills-insights')"href="#pills-insights"
                                role="tab">Insights</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-tags-tab" data-bs-toggle="pill" onclick="showTab('pills-tags')"href="#pills-tags"
                                role="tab">Tags</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-settings-tab" data-bs-toggle="pill" onclick="showTab('pills-settings')"href="#pills-settings"
                                role="tab">Settings</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-2 mb-3" id="pills-without-border-tabContent">
                        {{-- Tab contents extracted to partials --}}
                        @include('pages.repository.partials._files')
                        @include('pages.repository.partials._branches')
                        @include('pages.repository.partials._tags')
                        @include('pages.repository.partials._commits')
                        @include('pages.repository.partials._insights')
                        @include('pages.repository.partials._network')
                        @include('pages.repository.partials._members')
                        @include('pages.repository.partials._guide')
                        @include('pages.repository.partials._settings')
                    </div>
                </div>
            </div>
        </div>
    </div> @endsection

@push('modals')
    <!-- Google Font Fira Code -->
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/prism.css') }}" />

    <style>
        .code-viewer-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .code-viewer-container::-webkit-scrollbar-track {
            background: #1e1e1e;
        }
        .code-viewer-container::-webkit-scrollbar-thumb {
            background: #3c3c3c;
            border-radius: 4px;
        }
        .code-viewer-container::-webkit-scrollbar-thumb:hover {
            background: #4f4f4f;
        }
        pre[class*="language-"] {
            margin: 0 !important;
            padding: 1.5rem 1.5rem 1.5rem 4.5rem !important;
            background: #1e1e1e !important;
            border-radius: 0 !important;
            font-family: 'Fira Code', 'JetBrains Mono', Consolas, Monaco, monospace !important;
            font-size: 13.5px !important;
            line-height: 1.6 !important;
        }
        code[class*="language-"] {
            font-family: inherit !important;
            font-size: inherit !important;
            line-height: inherit !important;
        }
        .line-numbers .line-numbers-rows {
            border-right: 1px solid #3c3c3c !important;
            padding-right: 10px !important;
            left: -3.5rem !important;
            width: 3rem !important;
        }
        .line-numbers-rows > span:before {
            color: #757575 !important;
        }
        .file-toolbar {
            background-color: #151515 !important;
            border-bottom: 1px solid #2d2d2d !important;
        }
    </style>

    <!-- File Viewer Modal -->
    <div class="modal fade" id="fileViewerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 90%;">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 12px;">
                <div class="modal-header bg-dark text-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-code text-warning me-2 fa-lg"></i>
                        <h5 class="modal-title fw-bold" id="fileViewerTitle" style="font-size: 1.1rem; letter-spacing: 0.5px;">File Viewer</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="#" id="btn-download-file" class="btn btn-sm btn-outline-light me-3 px-3 py-1.5" style="border-radius: 6px;">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 bg-dark">
                    <!-- File Metadata Toolbar -->
                    <div class="file-toolbar d-flex align-items-center justify-content-between px-4 py-2 text-white-50" style="font-size: 0.85rem;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-1.5 text-info"></i>
                            <span id="file-meta-info">Loading file information...</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-link text-white-50 p-0 text-decoration-none" id="btn-copy-code" onclick="copyFileContent()">
                                <i class="far fa-copy me-1.5"></i>Copy Code
                            </button>
                        </div>
                    </div>
                    <!-- Code viewer area -->
                    <div class="code-viewer-container" style="max-height: 70vh; overflow-y: auto;">
                        <pre class="line-numbers"><code id="file-code-block" class="language-none"></code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commit Detail Modal -->
    <div class="modal fade" id="commitDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i> Commit Detail: <span id="modal-commit-hash-title"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="commit-modal-body-container">
                    <!-- Loading Spinner -->
                    <div class="text-center py-5" id="commit-loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Fetching commit details...</p>
                    </div>
                    <!-- Content (Initially hidden) -->
                    <div id="commit-content" style="display: none;">
                        <div class="card border mb-3">
                            <div class="card-body bg-light">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <strong>Author:</strong> <span id="commit-author"></span>
                                    </div>
                                    <div class="col-md-6 mb-2 text-md-end">
                                        <strong>Date:</strong> <span id="commit-date"></span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <strong>Message:</strong>
                                        <p class="mb-0 bg-white p-2 border rounded text-monospace" id="commit-message" style="white-space: pre-wrap; font-family: monospace;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3"><i class="fas fa-file-signature me-1 text-primary"></i> Changes / Diff:</h6>
                        <div id="commit-diffs"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge / Rebase Modal -->
    <div class="modal fade" id="mergeBranchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('repository.merge-rebase', $repository) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-code-branch me-2"></i> Git Merge / Rebase Branch
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold required">Action Type</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action_type" id="action_merge" value="merge" checked>
                                    <label class="form-check-label fw-semibold" for="action_merge">
                                        Merge Branch
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action_type" id="action_rebase" value="rebase">
                                    <label class="form-check-label fw-semibold" for="action_rebase">
                                        Rebase Branch
                                    </label>
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <b>Merge:</b> Combines changes and updates the Target branch.<br>
                                <b>Rebase:</b> Re-applies commits and updates the Source branch.
                            </small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="source_branch" class="required fw-bold">Source Branch (Cabang Asal)</label>
                            <select class="form-select form-control" id="source_branch" name="source_branch" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch }}">{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="target_branch" class="required fw-bold">Target Branch (Cabang Tujuan)</label>
                            <select class="form-select form-control" id="target_branch" name="target_branch" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }}>{{ $branch }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">Execute Action</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Branch Modal -->
    <div class="modal fade" id="createBranchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('repository.create-branch', $repository) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-code-branch me-2"></i> Create New Branch
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="branch_name" class="required fw-bold">Branch Name</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" required 
                                placeholder="e.g. feature/new-login" pattern="^[a-zA-Z0-9_\-\.\/]+$"
                                title="Branch name can only contain alphanumeric characters, dashes, underscores, dots, or slashes.">
                            <small class="form-text text-muted">No spaces or special characters allowed.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="from_branch" class="required fw-bold">Source Branch</label>
                            <select class="form-select form-control" id="from_branch" name="from_branch" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }}>
                                        {{ $branch }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">The new branch will copy files and commits from this branch.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Tag Modal -->
    <div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('repository.create-tag', $repository) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-tag me-2"></i> Create New Tag
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="tag_name" class="required fw-bold">Tag Name</label>
                            <input type="text" class="form-control" id="tag_name" name="tag_name" required 
                                placeholder="e.g. v1.0.0" pattern="^[a-zA-Z0-9_\-\.\/]+$"
                                title="Tag name can only contain alphanumeric characters, dashes, underscores, dots, or slashes.">
                            <small class="form-text text-muted">No spaces or special characters allowed.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="target" class="required fw-bold">Target (Branch or Commit SHA)</label>
                            <select class="form-select form-control" id="target" name="target" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }}>
                                        {{ $branch }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">The tag will point to the latest commit on this branch.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="message" class="fw-bold">Message (Optional)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                placeholder="Enter tag description/annotation..."></textarea>
                            <small class="form-text text-muted">Optional description for annotated tag.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">Create Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/prism.js') }}"></script>

    <script>
        function openFileViewer(path) {
            const branch = '{{ $selectedBranch }}';
            const url = '{{ route('repository.view-file', $repository) }}?path=' + encodeURIComponent(path) + '&branch=' +
                encodeURIComponent(branch);

            // Show loading state
            $('#file-meta-info').html('<i class="fas fa-spinner fa-spin me-1.5 text-info"></i>Loading file details...');
            $('#file-code-block').attr('class', 'language-none').text('Loading file content...');
            $('#fileViewerTitle').text(path);
            $('#fileViewerModal').modal('show');

            // Fetch content via AJAX
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    // 1. Determine Language class for Prism
                    const extension = path.split('.').pop().toLowerCase();
                    const langMap = {
                        'php': 'php',
                        'js': 'javascript',
                        'ts': 'typescript',
                        'json': 'json',
                        'html': 'html',
                        'css': 'css',
                        'md': 'markdown',
                        'py': 'python',
                        'sh': 'bash',
                        'sql': 'sql',
                        'xml': 'xml',
                        'yml': 'yaml',
                        'yaml': 'yaml',
                        'rb': 'ruby',
                        'go': 'go',
                        'rs': 'rust',
                        'java': 'java',
                        'c': 'c',
                        'cpp': 'cpp',
                        'cs': 'csharp',
                        'blade': 'html'
                    };
                    const lang = langMap[extension] || 'none';
                    
                    // 2. Put raw content in code block and set language class
                    const codeBlock = document.getElementById('file-code-block');
                    codeBlock.className = 'language-' + lang;
                    codeBlock.textContent = data.content;

                    // 3. Trigger Prism syntax highlighting
                    if (typeof Prism !== 'undefined') {
                        Prism.highlightElement(codeBlock);
                    }

                    // 4. Calculate file size and line counts
                    const linesCount = data.content.split('\n').length;
                    const byteSize = new Blob([data.content]).size;
                    let sizeStr = byteSize + ' B';
                    if (byteSize > 1024 * 1024) {
                        sizeStr = (byteSize / (1024 * 1024)).toFixed(2) + ' MB';
                    } else if (byteSize > 1024) {
                        sizeStr = (byteSize / 1024).toFixed(2) + ' KB';
                    }
                    
                    $('#file-meta-info').html(`
                        <span class="me-3"><i class="fas fa-list-ol me-1.5 text-primary"></i><strong>${linesCount}</strong> lines</span>
                        <span class="me-3"><i class="fas fa-hdd me-1.5 text-success"></i><strong>${sizeStr}</strong></span>
                        <span><i class="fas fa-code me-1.5 text-warning"></i>Type: <strong>${extension.toUpperCase()}</strong></span>
                    `);

                    // 5. Update download link
                    $('#btn-download-file').attr('href',
                        '{{ route('repository.download-file', $repository) }}?path=' +
                        encodeURIComponent(path) + '&branch=' + encodeURIComponent(branch));
                },
                error: function(xhr) {
                    $('#file-code-block').attr('class', 'language-none').html('<span class="text-danger">Error: ' + (xhr.responseJSON ? xhr
                        .responseJSON.error : 'Could not load file') + '</span>');
                    $('#file-meta-info').html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Failed to load file details.</span>');
                }
            });
        }

        function copyFileContent() {
            const codeBlock = document.getElementById('file-code-block');
            const textToCopy = codeBlock.textContent || codeBlock.innerText;

            navigator.clipboard.writeText(textToCopy).then(function() {
                // Change copy button UI temporarily to show success
                const btn = $('#btn-copy-code');
                const originalHtml = btn.html();
                btn.html('<i class="fas fa-check me-1.5 text-success"></i><strong>Copied!</strong>').addClass('text-success');
                setTimeout(() => {
                    btn.html(originalHtml).removeClass('text-success');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function showTab(tabId) {
            // 1. Sembunyikan semua tab-pane secara manual
            $('#pills-without-border-tabContent .tab-pane').removeClass('show active');
            
            // 2. Nonaktifkan semua link navigasi (baik yang terlihat maupun tersembunyi)
            $('#pills-tab-without-border .nav-link').removeClass('active');
            
            // 3. Tampilkan tab-pane yang dituju
            $('#' + tabId).addClass('show active');
            
            // 4. Aktifkan link trigger-nya (untuk sinkronisasi internal bootstrap)
            $('#' + tabId + '-tab').addClass('active');
            
            // Tambahan: Tutup dropdown kebab menu setelah diklik
            $('.dropdown-toggle').dropdown('hide');
        }

        // --- CHARTS ---
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Contribution Chart
            var ctxContribution = document.getElementById('contributionChart').getContext('2d');
            var contributionChart = new Chart(ctxContribution, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($contributionData['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($contributionData['data']) !!},
                        backgroundColor: ['#1d7af3', '#f3545d'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    },
                    layout: {
                        padding: {
                            top: 20
                        }
                    }
                }
            });

            // 2. Activity Chart
            var ctxActivity = document.getElementById('activityChart').getContext('2d');
            var activityChart = new Chart(ctxActivity, {
                type: 'line',
                data: {
                    labels: {!! json_encode($activityData['labels']) !!},
                    datasets: [{
                        label: "Commits",
                        borderColor: "#1d7af3",
                        pointBorderColor: "#FFF",
                        pointBackgroundColor: "#1d7af3",
                        pointBorderWidth: 2,
                        pointHoverRadius: 4,
                        pointHoverBorderWidth: 1,
                        pointRadius: 4,
                        backgroundColor: 'transparent',
                        fill: true,
                        borderWidth: 2,
                        data: {!! json_encode($activityData['data']) !!}
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        bodySpacing: 4,
                        mode: "nearest",
                        intersect: 0,
                        position: "nearest",
                        xPadding: 10,
                        yPadding: 10,
                        caretPadding: 10
                    },
                    layout: {
                        padding: {
                            left: 15,
                            right: 15,
                            top: 15,
                            bottom: 15
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "500",
                                beginAtZero: true,
                                maxTicksLimit: 5,
                                padding: 20,
                                stepSize: 1
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 20,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "500"
                            }
                        }]
                    }
                }
            });

            // 3. Weekly Productivity Chart (Bar)
            var ctxWeekly = document.getElementById('weeklyActivityChart').getContext('2d');
            var weeklyChart = new Chart(ctxWeekly, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($dayActivityData['labels']) !!},
                    datasets: [{
                        label: "Commits",
                        backgroundColor: '#59d05d',
                        borderColor: '#59d05d',
                        data: {!! json_encode($dayActivityData['data']) !!},
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1
                            }
                        }]
                    }
                }
            });

            // 4. Language Pie Chart
            var ctxLang = document.getElementById('languagePieChart').getContext('2d');
            var langChart = new Chart(ctxLang, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_column($languages, 'name')) !!},
                    datasets: [{
                        data: {!! json_encode(array_column($languages, 'percent')) !!},
                        backgroundColor: {!! json_encode(array_column($languages, 'color')) !!},
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'right'
                    }
                }
            });

            // 5. Open tab from URL query parameter
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                showTab('pills-' + tabParam);
            }

            // 6. Delete Branch SweetAlert Confirmation
            $('.form-delete-branch').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                var branchName = $(this).data('branch');
                swal({
                    title: "Apakah Anda yakin?",
                    text: "Branch '" + branchName + "' akan dihapus permanen dari repositori ini!",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: null,
                            visible: true,
                            className: "btn btn-secondary",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Ya, Hapus!",
                            value: true,
                            visible: true,
                            className: "btn btn-danger",
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });

            // 7. Delete Tag SweetAlert Confirmation
            $('.form-delete-tag').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                var tagName = $(this).data('tag');
                swal({
                    title: "Apakah Anda yakin?",
                    text: "Tag '" + tagName + "' akan dihapus permanen dari repositori ini!",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: null,
                            visible: true,
                            className: "btn btn-secondary",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Ya, Hapus!",
                            value: true,
                            visible: true,
                            className: "btn btn-danger",
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });

            // 8. Commit Detail AJAX Viewer
            $(document).on('click', '.commit-hash-link', function(e) {
                e.preventDefault();
                var hash = $(this).data('hash');
                $('#modal-commit-hash-title').text(hash.substring(0, 7));
                $('#commit-loading').show();
                $('#commit-content').hide();
                $('#commitDetailModal').modal('show');

                $.ajax({
                    url: "{{ route('repository.commit-detail', $repository) }}",
                    method: 'GET',
                    data: { hash: hash },
                    success: function(response) {
                        $('#commit-author').text(response.author);
                        $('#commit-date').text(response.date);
                        $('#commit-message').text(response.message);

                        var diffsHtml = '';
                        if (response.diffs.length === 0) {
                            diffsHtml = '<div class="alert alert-info">No file changes detected in this commit.</div>';
                        } else {
                            response.diffs.forEach(function(file) {
                                diffsHtml += '<div class="card border mb-3">';
                                diffsHtml += '  <div class="card-header bg-dark text-white py-2 fw-semibold d-flex align-items-center justify-content-between">';
                                diffsHtml += '    <span><i class="far fa-file me-2"></i>' + file.filename + '</span>';
                                diffsHtml += '  </div>';
                                diffsHtml += '  <div class="card-body p-0">';
                                diffsHtml += '    <pre class="mb-0" style="font-family: monospace; font-size: 13px; line-height: 1.5; overflow-x: auto; max-height: 400px; white-space: pre;">';
                                
                                file.lines.forEach(function(line) {
                                    var lineClass = 'px-3 d-block';
                                    if (line.type === 'addition') {
                                        lineClass += ' bg-success-light text-success-dark';
                                    } else if (line.type === 'deletion') {
                                        lineClass += ' bg-danger-light text-danger-dark';
                                    } else if (line.type === 'info') {
                                        lineClass += ' bg-info-light text-info-dark';
                                    }
                                    
                                    // Escape HTML
                                    var escapedContent = $('<div>').text(line.content).html();
                                    diffsHtml += '<span class="' + lineClass + '">' + escapedContent + '</span>';
                                });

                                diffsHtml += '    </pre>';
                                diffsHtml += '  </div>';
                                diffsHtml += '</div>';
                            });
                        }

                        $('#commit-diffs').html(diffsHtml);
                        $('#commit-loading').hide();
                        $('#commit-content').show();
                    },
                    error: function() {
                        $('#commit-loading').hide();
                        $('#commit-diffs').html('<div class="alert alert-danger mb-0">Failed to load commit details.</div>');
                        $('#commit-content').show();
                    }
                });
            });
        });
    </script>
@endpush
