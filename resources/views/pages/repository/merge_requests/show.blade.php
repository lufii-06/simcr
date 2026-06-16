@extends('dashboard')

@section('title', 'Merge Request #' . $mergeRequest->id)

@section('content')
<style>
    .bg-success-light { background-color: rgba(40, 167, 69, 0.15) !important; }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.15) !important; }
    .bg-info-light { background-color: rgba(23, 162, 184, 0.1) !important; }
    .text-success-dark { color: #1e7e34 !important; }
    .text-danger-dark { color: #bd2130 !important; }
    .text-info-dark { color: #117a8b !important; }
</style>

<div class="page-inner">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PR Header -->
    <div class="card mb-4 border">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">
                        {{ $mergeRequest->title }} <span class="text-muted fw-light">#{{ $mergeRequest->id }}</span>
                    </h2>
                    <div class="d-flex align-items-center flex-wrap gap-2 text-muted small mt-2">
                        @if ($mergeRequest->status === 'open')
                            <span class="badge badge-success px-3 py-1.5"><i class="fas fa-dot-circle me-1"></i> Open</span>
                        @elseif ($mergeRequest->status === 'merged')
                            <span class="badge px-3 py-1.5 text-white" style="background-color: #6f42c1 !important;"><i class="fas fa-code-merge me-1"></i> Merged</span>
                        @else
                            <span class="badge badge-danger px-3 py-1.5"><i class="fas fa-times-circle me-1"></i> Closed</span>
                        @endif
                        <span><strong>{{ $mergeRequest->user->name ?? 'System' }}</strong> wants to merge commits from <code class="text-danger bg-light px-1.5 py-0.5 rounded">{{ $mergeRequest->source_branch }}</code> into <code class="text-primary bg-light px-1.5 py-0.5 rounded">{{ $mergeRequest->target_branch }}</code></span>
                        <span class="mx-1">•</span>
                        <span>Opened {{ $mergeRequest->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div>
                    <a href="{{ route('repository.show', ['repository' => $repository->name, 'tab' => 'merges']) }}" class="btn btn-outline-secondary btn-sm btn-round mt-2 mt-sm-0">
                        <i class="fas fa-arrow-left me-1"></i> Back to Merge Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PR Merge Actions Area -->
    @if ($mergeRequest->status === 'open')
        <div class="card border mb-4 bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-title rounded-circle border border-white bg-success">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">This Merge Request is ready to merge</h5>
                            <p class="text-muted small mb-0">No conflicts detected. Code changes can be integrated automatically.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if (auth()->user()->role !== 'client' && auth()->user()->role !== 'developer')
                            <form action="{{ route('repository.merge-requests.merge', [$repository->name, $mergeRequest->id]) }}" method="POST" id="form-merge-pr">
                                @csrf
                                <button type="submit" class="btn btn-success" id="btn-merge-pr">
                                    <i class="fas fa-code-merge me-1"></i> Merge Request
                                </button>
                            </form>
                        @endif
                        @if (auth()->id() === $mergeRequest->user_id || auth()->user()->role === 'pm' || auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
                            <form action="{{ route('repository.merge-requests.close', [$repository->name, $mergeRequest->id]) }}" method="POST" id="form-close-pr">
                                @csrf
                                <button type="submit" class="btn btn-danger" id="btn-close-pr">
                                    <i class="fas fa-ban me-1"></i> Close Merge Request
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif ($mergeRequest->status === 'merged')
        <div class="card border mb-4 bg-purple-light" style="background-color: rgba(111, 66, 193, 0.08);">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3">
                        <span class="avatar-title rounded-circle border border-white text-white" style="background-color: #6f42c1;">
                            <i class="fas fa-code-merge"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Merge Request Merged</h5>
                        <p class="text-muted small mb-0">Changes were successfully merged into <code>{{ $mergeRequest->target_branch }}</code> branch.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border mb-4 bg-danger-light">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3">
                        <span class="avatar-title rounded-circle border border-white bg-danger">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Merge Request Closed</h5>
                        <p class="text-muted small mb-0">This Merge Request was closed without merging code changes.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="prTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="pr-overview-tab" data-bs-toggle="tab" data-bs-target="#pr-overview-pane" type="button" role="tab" style="border: 1px solid #dee2e6; margin-right: 4px;">
                <i class="fas fa-align-left me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="pr-commits-tab" data-bs-toggle="tab" data-bs-target="#pr-commits-pane" type="button" role="tab" style="border: 1px solid #dee2e6; margin-right: 4px;">
                <i class="fas fa-history me-1"></i> Commits ({{ count($commits) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="pr-files-tab" data-bs-toggle="tab" data-bs-target="#pr-files-pane" type="button" role="tab" style="border: 1px solid #dee2e6;">
                <i class="fas fa-file-signature me-1"></i> File Changes ({{ count($diffs) }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="prTabsContent">
        <!-- Overview Pane -->
        <div class="tab-pane fade show active" id="pr-overview-pane" role="tabpanel">
            <div class="card border">
                <div class="card-header bg-light">
                    <h5 class="fw-bold mb-0 text-dark">Description</h5>
                </div>
                <div class="card-body">
                    @if ($mergeRequest->description)
                        <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $mergeRequest->description }}</div>
                    @else
                        <p class="text-muted mb-0 italic">No description provided.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Commits Pane -->
        <div class="tab-pane fade" id="pr-commits-pane" role="tabpanel">
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
                    <tbody>
                        @forelse ($commits as $commit)
                            <tr>
                                <td><code class="text-primary fw-bold">{{ substr($commit['hash'], 0, 7) }}</code></td>
                                <td>{{ $commit['message'] }}</td>
                                <td>{{ $commit['author'] }}</td>
                                <td class="text-nowrap">{{ $commit['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No unique commits found in source branch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- File Changes Pane -->
        <div class="tab-pane fade" id="pr-files-pane" role="tabpanel">
            @forelse ($diffs as $file)
                <div class="card border mb-3">
                    <div class="card-header bg-dark text-white py-2 fw-semibold">
                        <i class="far fa-file me-2"></i>{{ $file['filename'] }}
                    </div>
                    <div class="card-body p-0">
                        <pre class="mb-0" style="font-family: monospace; font-size: 13px; line-height: 1.5; overflow-x: auto; max-height: 450px; white-space: pre;">@foreach ($file['lines'] as $line)@php
    $lineClass = 'px-3 d-block';
    if ($line['type'] === 'addition') {
        $lineClass .= ' bg-success-light text-success-dark';
    } elseif ($line['type'] === 'deletion') {
        $lineClass .= ' bg-danger-light text-danger-dark';
    } elseif ($line['type'] === 'info') {
        $lineClass .= ' bg-info-light text-info-dark';
    }
@endphp<span class="{{ $lineClass }}">{{ $line['content'] }}</span>@endforeach</pre>
                    </div>
                </div>
            @empty
                <div class="alert alert-info py-4 text-center mb-0">No file changes detected.</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Merge PR SweetAlert Confirmation
        $('#btn-merge-pr').on('click', function(e) {
            e.preventDefault();
            var form = $('#form-merge-pr');
            swal({
                title: "Konfirmasi Penggabungan",
                text: "Apakah Anda yakin ingin memproses merge Merge Request #{{ $mergeRequest->id }} ke dalam cabang target '{{ $mergeRequest->target_branch }}'?",
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
                        text: "Ya, Merge!",
                        value: true,
                        visible: true,
                        className: "btn btn-success",
                        closeModal: true
                    }
                },
                dangerMode: false,
            }).then((willMerge) => {
                if (willMerge) {
                    form.submit();
                }
            });
        });

        // Close PR SweetAlert Confirmation
        $('#btn-close-pr').on('click', function(e) {
            e.preventDefault();
            var form = $('#form-close-pr');
            swal({
                title: "Konfirmasi Penutupan",
                text: "Apakah Anda yakin ingin menutup Merge Request #{{ $mergeRequest->id }} tanpa menggabungkannya?",
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
                        text: "Ya, Tutup!",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    }
                },
                dangerMode: true,
            }).then((willClose) => {
                if (willClose) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
