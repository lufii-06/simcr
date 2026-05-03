@extends('dashboard')

@section('title', 'Repository Detail')

@section('content')

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
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title me-3">Repository Explorer</h4>
                            <select class="form-select form-select-sm" style="width: auto;"
                                onchange="window.location.href='?branch=' + this.value">
                                @forelse ($branches as $branch)
                                    <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }}>
                                        {{ $branch }}
                                    </option>
                                @empty
                                    <option disabled selected>No branches found</option>
                                @endforelse
                            </select>
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
                            <a class="nav-link active" id="pills-files-tab" data-bs-toggle="pill" href="#pills-files"
                                role="tab" aria-controls="pills-files" aria-selected="true">
                                <i class="fas fa-folder-open me-1"></i> Files
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-branches-tab" data-bs-toggle="pill" href="#pills-branches"
                                role="tab" aria-controls="pills-branches" aria-selected="false">
                                <i class="fas fa-code-branch me-1"></i> Branches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-commits-tab" data-bs-toggle="pill" href="#pills-commits"
                                role="tab" aria-controls="pills-commits" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Commits
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-network-tab" data-bs-toggle="pill" href="#pills-network"
                                role="tab" aria-controls="pills-network" aria-selected="false">
                                <i class="fas fa-project-diagram me-1"></i> Network
                            </a>
                        </li>
                        {{-- Hidden Triggers for Kebab Menu --}}
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-insights-tab" data-bs-toggle="pill" href="#pills-insights"
                                role="tab">Insights</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-tags-tab" data-bs-toggle="pill" href="#pills-tags"
                                role="tab">Tags</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-settings-tab" data-bs-toggle="pill" href="#pills-settings"
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
    </div>
@endsection

@push('modals')
    <!-- File Viewer Modal -->
    <div class="modal fade" id="fileViewerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 90%;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="fileViewerTitle">File Viewer</h5>
                    <div class="ms-auto">
                        <a href="#" id="btn-download-file" class="btn btn-sm btn-outline-light me-2">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 bg-dark">
                    <pre id="file-content-area" class="m-0 p-3 text-light"
                        style="max-height: 70vh; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 14px; line-height: 1.5; white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openFileViewer(path) {
            const branch = '{{ $selectedBranch }}';
            const url = '{{ route('repository.view-file', $repository) }}?path=' + encodeURIComponent(path) + '&branch=' +
                encodeURIComponent(branch);

            // Show loading state
            $('#file-content-area').text('Loading file content...');
            $('#fileViewerTitle').text(path);
            $('#fileViewerModal').modal('show');

            // Fetch content via AJAX
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#file-content-area').text(data.content);
                    $('#btn-download-file').attr('href',
                        '{{ route('repository.download-file', $repository) }}?path=' +
                        encodeURIComponent(path) + '&branch=' + encodeURIComponent(branch));
                },
                error: function(xhr) {
                    $('#file-content-area').html('<span class="text-danger">Error: ' + (xhr.responseJSON ? xhr
                        .responseJSON.error : 'Could not load file') + '</span>');
                }
            });
        }

        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            var textToCopy = "";

            if (copyText.tagName === 'INPUT' || copyText.tagName === 'TEXTAREA') {
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                textToCopy = copyText.value;
            } else {
                textToCopy = copyText.innerText || copyText.textContent;
            }

            // Using modern Clipboard API
            navigator.clipboard.writeText(textToCopy).then(function() {
                $.notify({
                    icon: 'fa fa-check',
                    title: 'Copied!',
                    message: 'Copied to clipboard successfully',
                }, {
                    type: 'success',
                    placement: {
                        from: "top",
                        align: "right"
                    },
                    time: 1000,
                });
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                document.execCommand("copy");
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
        });
    </script>
@endpush
