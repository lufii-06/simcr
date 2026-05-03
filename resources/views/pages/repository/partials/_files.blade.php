<div class="tab-pane fade show active" id="pills-files" role="tabpanel" aria-labelledby="pills-files-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h6 class="fw-bold mb-0"><i class="fas fa-folder-open me-1"></i> Files in Branch: <span class="text-info">{{ $selectedBranch }}</span></h6>
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
                                <i class="fas fa-folder text-warning me-2"></i>
                                <span class="fw-bold">{{ $file['name'] }}</span>
                            @else
                                <i class="fas fa-file-code text-primary me-2"></i>
                                <a href="javascript:void(0)" onclick="openFileViewer('{{ $file['name'] }}')"
                                    class="fw-bold text-primary">{{ $file['name'] }}</a>
                            @endif
                        </td>
                        <td class="text-end text-muted">{{ $file['size'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">Empty repository
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($readme)
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