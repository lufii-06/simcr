<div class="tab-pane fade show active" id="pills-files" role="tabpanel" aria-labelledby="pills-files-tab">
    
    {{-- Dynamic Breadcrumb Navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-3 px-2 flex-wrap">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 py-1 bg-transparent px-0" style="font-size: 0.95rem;">
                <li class="breadcrumb-item">
                    <a href="?branch={{ urlencode($selectedBranch) }}" class="text-secondary fw-semibold">
                        <i class="fas fa-home me-1"></i> Root
                    </a>
                </li>
                @if ($path)
                    @php
                        $segments = explode('/', $path);
                        $currentPath = '';
                    @endphp
                    @foreach ($segments as $segment)
                        @php
                            $currentPath = $currentPath ? $currentPath . '/' . $segment : $segment;
                        @endphp
                        @if ($loop->last)
                            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ $segment }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="?branch={{ urlencode($selectedBranch) }}&path={{ urlencode($currentPath) }}" class="text-secondary fw-semibold">
                                    {{ $segment }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ol>
        </nav>
        <h6 class="fw-bold mb-0 text-muted small mt-1 mt-sm-0">
            <i class="fas fa-code-branch me-1"></i> Branch: <span class="text-info">{{ $selectedBranch }}</span>
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-end">Size</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($files as $file)
                    <tr>
                        <td>
                            @if ($file['type'] === 'tree')
                                @if (isset($file['is_parent']) && $file['is_parent'])
                                    <i class="fas fa-arrow-left text-muted me-2"></i>
                                    <a href="?branch={{ urlencode($selectedBranch) }}&path={{ urlencode($file['path']) }}"
                                        class="fw-bold text-muted">{{ $file['name'] }}</a>
                                @else
                                    <i class="fas fa-folder text-warning me-2"></i>
                                    <a href="?branch={{ urlencode($selectedBranch) }}&path={{ urlencode($file['path']) }}"
                                        class="fw-bold text-secondary">{{ $file['name'] }}</a>
                                @endif
                            @else
                                <i class="fas fa-file-code text-primary me-2"></i>
                                <a href="javascript:void(0)" onclick="openFileViewer('{{ $file['path'] }}')"
                                    class="fw-bold text-primary">{{ $file['name'] }}</a>
                            @endif
                        </td>
                        <td class="text-end text-muted">{{ $file['size'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">Empty repository or folder
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($readme && !$path) {{-- Only show README at the root path --}}
        <div class="card mt-4 border">
            <div class="card-header bg-light py-2">
                <div class="card-title small fw-bold"><i class="fas fa-book me-2"></i> README.md
                </div>
            </div>
            <div class="card-body">
                <pre
                    style="white-space: pre-wrap; font-family: inherit; font-size: 0.9rem; line-height: 1.6;">{{ $readme }}</pre>
            </div>
        </div>
    @endif
</div>