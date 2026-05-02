<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo">
                <img src="{{ asset('images/logo-color.png') }}" alt="navbar brand" class="navbar-brand pt-2"
                    height="150" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Home</h4>
                </li>
                <li class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @if (auth()->user()->role == 'pm')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Master Data</h4>
                    </li>
                    <li class="nav-item {{ Request::is('user*') ? 'active' : '' }}">
                        <a href="{{ route('user.index') }}">
                            <i class="fas fa-user"></i>
                            <p>User</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('client*') ? 'active' : '' }}">
                        <a href="{{ route('client.index') }}">
                            <i class="fas fa-users"></i>
                            <p>Client</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('developer*') ? 'active' : '' }}">
                        <a href="{{ route('developer.index') }}">
                            <i class="fas fa-user-tag"></i>
                            <p>Developer</p>
                        </a>
                    </li>
                @endif

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Transaction</h4>
                </li>
                <li class="nav-item {{ Request::is('project*') || Request::is('repository*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#projectNav">
                        <i class="fas fa-project-diagram"></i>
                        <p>Projects</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ Request::is('project*') || Request::is('repository*') ? 'show' : '' }}"
                        id="projectNav">
                        <ul class="nav nav-collapse">
                            <li class="{{ Request::is('project*') ? 'active' : '' }}">
                                <a href="{{ route('project.index') }}">
                                    <span class="sub-item">List Project</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('repository*') ? 'active' : '' }}">
                                <a href="{{ route('repository.index') }}">
                                    <span class="sub-item">Repository</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ Request::is('task*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#taskNav">
                        <i class="fas fa-tasks"></i>
                        <p>Tasks</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ Request::is('task*') ? 'show' : '' }}" id="taskNav">
                        <ul class="nav nav-collapse">
                            <li class="{{ Request::is('task') && Request::get('type') != 'my' ? 'active' : '' }}">
                                <a href="{{ route('task.index') }}">
                                    <span class="sub-item">All Task</span>
                                </a>
                            </li>
                            <li class="{{ Request::get('type') == 'my' ? 'active' : '' }}">
                                <a href="{{ route('task.index', ['type' => 'my']) }}">
                                    <span class="sub-item">My Task</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                @if (auth()->user()->role == 'pm')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Reports</h4>
                    </li>
                    <li class="nav-item {{ Request::routeIs('report.index') ? 'active' : '' }}">
                        <a href="{{ route('report.index') }}">
                            <i class="fas fa-file-contract"></i>
                            <p>Laporan</p>
                        </a>
                    </li>

                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Others</h4>
                    </li>
                    <li class="nav-item {{ Request::is('setting*') ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" href="#base">
                            <i class="fas fa-cog"></i>
                            <p>Settings</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse {{ Request::is('setting*') ? 'show' : '' }}" id="base">
                            <ul class="nav nav-collapse">
                                <li class="{{ Request::routeIs('developer-status.*') ? 'active' : '' }}">
                                    <a href="{{ route('developer-status.index') }}">
                                        <span class="sub-item">Developer Status</span>
                                    </a>
                                </li>
                                <li class="{{ Request::routeIs('project-status.*') ? 'active' : '' }}">
                                    <a href="{{ route('project-status.index') }}">
                                        <span class="sub-item">Project Status</span>
                                    </a>
                                </li>
                                <li class="{{ Request::routeIs('task-status.*') ? 'active' : '' }}">
                                    <a href="{{ route('task-status.index') }}">
                                        <span class="sub-item">Task Status</span>
                                    </a>
                                </li>
                                <li class="{{ Request::routeIs('specialization.*') ? 'active' : '' }}">
                                    <a href="{{ route('specialization.index') }}">
                                        <span class="sub-item">Specialization</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->