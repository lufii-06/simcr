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
                                    <div class="text-muted small">Project: {{ $repository->project->name ?? 'No Project' }}</div>
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
                                            value="{{ env('REPO_ROOT_URL', 'git@localhost') }}:repositories/{{ $repository->name ?? 'unknown' }}.git"
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
                                        @php
                                            $httpRoot = request()->getSchemeAndHttpHost();
                                            $tokenPart =
                                                !($repository->is_public ?? true) && ($repository->access_token ?? false)
                                                    ? $repository->access_token . '@'
                                                    : '';
                                            $cleanHttpRoot = str_replace(['http://', 'https://'], '', $httpRoot);
                                            $protocol = request()->getScheme();
                                            $httpUrl = "{$protocol}://{$tokenPart}{$cleanHttpRoot}/repositories/" . ($repository->name ?? 'unknown') . ".git";
                                        @endphp
                                        <input type="text" class="form-control form-control-sm bg-light" id="http-url"
                                            value="{{ $httpUrl }}" readonly>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card card-profile">
                <div class="card-header" style="background-image: url('{{ asset('assets/img/blogpost.jpg') }}')">
                    <div class="profile-picture">
                        <div class="avatar avatar-xl">
                            <span class="avatar-title rounded-circle border border-white bg-primary"><i
                                    class="fas fa-info-circle fa-2x"></i></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile text-center">
                        <div class="name">Repository Statistics</div>
                        <div class="job">Overall health and details</div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row user-stats text-center">
                        <div class="col">
                            <div class="number">{{ count($branches ?? []) }}</div>
                            <div class="title">Branches</div>
                        </div>
                        <div class="col">
                            <div class="number">{{ count($tags ?? []) }}</div>
                            <div class="title">Tags</div>
                        </div>
                        <div class="col">
                            <div class="number">{{ count($recentCommits ?? []) }}</div>
                            <div class="title">Commits</div>
                        </div>
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
                                role="tab" aria-controls="pills-files" aria-selected="true">Files</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-branches-tab" data-bs-toggle="pill" href="#pills-branches"
                                role="tab" aria-controls="pills-branches" aria-selected="false">Branches</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-tags-tab" data-bs-toggle="pill" href="#pills-tags" role="tab"
                                aria-controls="pills-tags" aria-selected="false">Tags</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-commits-tab" data-bs-toggle="pill" href="#pills-commits"
                                role="tab" aria-controls="pills-commits" aria-selected="false">Commits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-insights-tab" data-bs-toggle="pill" href="#pills-insights"
                                role="tab" aria-controls="pills-insights" aria-selected="false">Insights</a>
                        </li>
                        {{-- Hidden Triggers for Kebab Menu --}}
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-members-tab" data-bs-toggle="pill" href="#pills-members"
                                role="tab">Members</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-guide-tab" data-bs-toggle="pill" href="#pills-guide"
                                role="tab">Guide</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="pills-settings-tab" data-bs-toggle="pill" href="#pills-settings"
                                role="tab">Settings</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-2 mb-3" id="pills-without-border-tabContent">
                        <div class="tab-pane fade show active" id="pills-files" role="tabpanel"
                            aria-labelledby="pills-files-tab">
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
                                                        <a href="javascript:void(0)"
                                                            onclick="openFileViewer('{{ $file['name'] }}')"
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
                        <div class="tab-pane fade" id="pills-branches" role="tabpanel"
                            aria-labelledby="pills-branches-tab">
                            <div class="list-group list-group-messages list-group-flush">
                                @forelse ($branches as $branch)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-code-branch text-info me-3"></i>
                                            <span class="fw-bold">{{ $branch }}</span>
                                            @if ($branch === $repository->default_branch)
                                                <span class="badge badge-primary badge-xs ms-2">Default</span>
                                            @endif
                                            <a href="?branch={{ $branch }}" class="btn btn-xs btn-link ms-auto">View
                                                Commits</a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">No branches found.</div>
                                @endforelse
                            </div>
                        </div>
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
                        <div class="tab-pane fade" id="pills-commits" role="tabpanel" aria-labelledby="pills-commits-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                <h6 class="fw-bold mb-0">History for branch: <span
                                        class="text-info">{{ $selectedBranch }}</span></h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hash</th>
                                            <th>Message</th>
                                            <th>Author</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentCommits as $commit)
                                            <tr>
                                                <td><code class="text-primary">{{ $commit['hash'] }}</code></td>
                                                <td style="max-width: 300px;" class="text-truncate">
                                                    {{ $commit['message'] }}</td>
                                                <td>{{ $commit['author'] }}</td>
                                                <td class="text-nowrap">{{ $commit['date'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No commits found for
                                                    this branch.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-insights" role="tabpanel" aria-labelledby="pills-insights-tab">
                            @if (!empty($languages))
                                <div class="card mb-4">
                                    <div class="card-header py-2">
                                        <div class="card-title small fw-bold">Language Statistics</div>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="progress mb-2" style="height: 10px;">
                                            @foreach ($languages as $lang)
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ $lang['percent'] }}%; background-color: {{ $lang['color'] }};"
                                                    aria-valuenow="{{ $lang['percent'] }}" aria-valuemin="0"
                                                    aria-valuemax="100" data-bs-toggle="tooltip"
                                                    title="{{ $lang['name'] }}: {{ $lang['percent'] }}%"></div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex flex-wrap">
                                            @foreach ($languages as $lang)
                                                <div class="d-flex align-items-center me-4 mt-1">
                                                    <span class="rounded-circle me-2"
                                                        style="width: 10px; height: 10px; background-color: {{ $lang['color'] }};"></span>
                                                    <span class="small fw-bold">{{ $lang['name'] }}</span>
                                                    <span class="small text-muted ms-1">{{ $lang['percent'] }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-stats card-round">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="icon-big text-center icon-primary">
                                                        <i class="fas fa-hdd"></i>
                                                    </div>
                                                </div>
                                                <div class="col-7 col-stats">
                                                    <div class="numbers">
                                                        <p class="card-category">Repo Size</p>
                                                        <h4 class="card-title">{{ $stats['size'] }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-stats card-round">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="icon-big text-center icon-success">
                                                        <i class="fas fa-file-code"></i>
                                                    </div>
                                                </div>
                                                <div class="col-7 col-stats">
                                                    <div class="numbers">
                                                        <p class="card-category">Total Files</p>
                                                        <h4 class="card-title">{{ $stats['files'] }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-stats card-round">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="icon-big text-center icon-info">
                                                        <i class="fas fa-cubes"></i>
                                                    </div>
                                                </div>
                                                <div class="col-7 col-stats">
                                                    <div class="numbers">
                                                        <p class="card-category">Git Objects</p>
                                                        <h4 class="card-title">{{ $stats['objects'] }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="card-title">User Contribution</div>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="contributionChart" style="width: 50%; height: 50%"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="card-title">Activity (30 Days)</div>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="activityChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-members" role="tabpanel" aria-labelledby="pills-members-tab">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item bg-light fw-bold">Project Owner</div>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span
                                                class="avatar-title rounded-circle border border-white bg-info">{{ substr($repository->project->owner->name ?? 'O', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $repository->project->owner->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $repository->project->owner->email ?? '' }}
                                            </div>
                                        </div>
                                        <span class="badge badge-info ms-auto">Owner</span>
                                    </div>
                                </div>

                                <div class="list-group-item bg-light fw-bold mt-3">Developers</div>
                                @forelse ($repository->project->developers as $dev)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span
                                                    class="avatar-title rounded-circle border border-white bg-secondary">{{ substr($dev->user->name ?? 'D', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $dev->user->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $dev->user->email ?? '' }}</div>
                                            </div>
                                            <span class="badge badge-secondary ms-auto">{{ $dev->role->name ?? 'Member' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center py-3 text-muted">No developers assigned.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-guide" role="tabpanel" aria-labelledby="pills-guide-tab">
                            <div class="p-3">
                                <h5 class="fw-bold mb-3">How to Clone this Repository</h5>

                                <div class="accordion accordion-secondary">
                                    <div class="card">
                                        <div class="card-header" id="headingOne" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            <div class="span-icon">
                                                <div class="fas fa-key"></div>
                                            </div>
                                            <div class="span-title">
                                                Option 1: Using SSH (Recommended)
                                            </div>
                                            <div class="span-mode"></div>
                                        </div>

                                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne">
                                            <div class="card-body">
                                                <p class="small text-muted">SSH is the most secure way to interact with Git without entering credentials every time.</p>
                                                
                                                <div class="position-relative">
                                                    <pre class="bg-dark text-light p-3 rounded" id="guide-ssh"><code>git clone {{ env('REPO_ROOT_URL', 'git@localhost') }}:repositories/{{ $repository->name }}.git</code></pre>
                                                    <button class="btn btn-xs btn-primary position-absolute"
                                                        style="top: 10px; right: 10px;"
                                                        onclick="copyToClipboard('guide-ssh')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>

                                                <h6 class="fw-bold small mt-3">Step-by-step:</h6>
                                                <ol class="small ps-3">
                                                    <li>Ensure you have an SSH Key on your computer. Check with <code>ls -al ~/.ssh</code>.</li>
                                                    <li>If you don't have one, generate it using: <code>ssh-keygen -t ed25519 -C "your_email@example.com"</code>.</li>
                                                    <li>Add your **Public Key** (<code>id_ed25519.pub</code>) to your SIMCR User Profile.</li>
                                                    <li>Copy the clone command above and run it in your terminal.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header collapsed" id="headingTwo" data-bs-toggle="collapse"
                                            data-bs-target="#collapseTwo" aria-expanded="false"
                                            aria-controls="collapseTwo">
                                            <div class="span-icon">
                                                <div class="fas fa-link"></div>
                                            </div>
                                            <div class="span-title">
                                                Option 2: Using HTTP
                                            </div>
                                            <div class="span-mode"></div>
                                        </div>
                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo">
                                            <div class="card-body">
                                                @if ($repository->is_public)
                                                    <p class="text-success small"><i class="fas fa-check-circle"></i> This
                                                        repository is <b>Public</b>. Anyone with the URL can clone it.</p>
                                                @else
                                                    <p class="text-warning small"><i
                                                            class="fas fa-exclamation-circle"></i> This repository is
                                                        <b>Private</b>.</p>
                                                    
                                                    @if($repository->access_token)
                                                        <div class="alert alert-info py-2 small">
                                                            <i class="fas fa-info-circle"></i> <b>Access Token:</b> We have embedded your token into the URL. You won't be asked for a password.
                                                        </div>
                                                    @else
                                                        <div class="alert alert-danger py-2 small">
                                                            <i class="fas fa-exclamation-triangle"></i> <b>Action Required!</b> This is a private repository. Please generate an Access Token in <b>Settings</b> first.
                                                            <div class="mt-2 text-end">
                                                                <button class="btn btn-xs btn-danger" onclick="showTab('pills-settings')">Generate Token Now</button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                <div class="position-relative">
                                                    <pre class="bg-dark text-light p-3 rounded" id="guide-http"><code>git clone {{ $httpUrl }}</code></pre>
                                                    <button class="btn btn-xs btn-secondary position-absolute"
                                                        style="top: 10px; right: 10px;"
                                                        onclick="copyToClipboard('guide-http')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>

                                                <h6 class="fw-bold small mt-3">Step-by-step:</h6>
                                                <ol class="small ps-3">
                                                    <li>Copy the HTTP URL provided above.</li>
                                                    @if(!$repository->is_public)
                                                        <li>Ensure your Access Token is active (Check the Settings tab).</li>
                                                    @endif
                                                    <li>Paste the command in your terminal and press Enter.</li>
                                                    <li>If prompted for a password, use your **Access Token** (or leave blank if it's already in the URL).</li>
                                                </ol>

                                                <div class="bg-light p-2 mt-3 rounded border">
                                                    <p class="small mb-0 text-muted"><b>Note:</b> Access tokens are a more secure alternative to using your account password for Git operations.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-settings" role="tabpanel"
                            aria-labelledby="pills-settings-tab">
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
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $repository->is_public ? 'warning' : 'primary' }}">
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
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $repository->status === 'active' ? 'danger' : 'success' }}">
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
                                                <input type="text" class="form-control form-control-sm" id="token-value" value="{{ $repository->access_token }}" readonly>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard('token-value')">
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
        });
    </script>
@endpush
