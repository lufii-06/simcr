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
                            aria-valuenow="{{ $lang['percent'] }}" aria-valuemin="0" aria-valuemax="100"
                            data-bs-toggle="tooltip" title="{{ $lang['name'] }}: {{ $lang['percent'] }}%"></div>
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
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="contributionChart"></canvas>
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
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Weekly Productivity</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="weeklyActivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Language Composition</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="languagePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
