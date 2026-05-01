<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'SIMCR')</title>
    @include('layouts.css')
</head>

<body>
    <div class="wrapper">
        @include('layouts.sidebar')
        <div class="main-panel">
            <div class="main-header">
                @include('layouts.navbar')
            </div>
            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h4 class="page-title">@yield('title', 'SIMCR')</h4>
                        <ul class="breadcrumbs">
                            <li class="nav-home">
                                <a href="{{ route('dashboard') }}">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                            @php $segments = ''; @endphp
                            @foreach (Request::segments() as $segment)
                                @php
                                    $segments .= '/' . $segment;
                                    // Skip plain text IDs and long encrypted IDs
                                    if (is_numeric($segment) || strlen($segment) > 30) {
                                        continue;
                                    }
                                @endphp
                                <li class="separator">
                                    <i class="icon-arrow-right"></i>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url($segments) }}">{{ ucwords(str_replace(['-', '_'], ' ', $segment)) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="page-category">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footer')
    </div>

    <!-- Global Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold"> Record</span>
                        <span class="fw-light"> Details </span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.js')
    <style>
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</body>

</html>
