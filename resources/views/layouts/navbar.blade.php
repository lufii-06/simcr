 <div class="main-header-logo">
     <!-- Logo Header -->
     <div class="logo-header" data-background-color="dark">
         <a href="{{ route('dashboard') }}" class="logo">
             <img src="{{ asset('images/logo-color.png') }}" alt="navbar brand" class="navbar-brand" height="20" />
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
 <!-- Navbar Header -->
 <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
     <div class="container-fluid">
         <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
             style="position: relative;">
             <div class="input-group">
                 <div class="input-group-prepend">
                     <button type="button" class="btn btn-search pe-1">
                         <i class="fa fa-search search-icon"></i>
                     </button>
                 </div>
                 <input type="text" id="globalProjectSearch" placeholder="Search Project..." class="form-control"
                     autocomplete="off" />
             </div>
             <ul id="globalProjectSearchResults" class="dropdown-menu w-100 mt-2 p-0 shadow-sm"
                 style="position: absolute; top: 100%; left: 0; display: none;">
                 <!-- Results go here -->
             </ul>
         </nav>

         <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
             <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                 <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                     aria-expanded="false" aria-haspopup="true">
                     <i class="fa fa-search"></i>
                 </a>
                 <ul class="dropdown-menu dropdown-search animated fadeIn">
                     <form class="navbar-left navbar-form nav-search">
                         <div class="input-group">
                             <input type="text" placeholder="Search ..." class="form-control" />
                         </div>
                     </form>
                 </ul>
             </li>

             <li class="nav-item topbar-icon dropdown hidden-caret">
                 <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                     data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                     onclick="markAllNotificationsAsRead()">
                     <i class="fa fa-bell"></i>
                     @if (auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                         <span class="notification"
                             id="notif-count">{{ auth()->user()->unreadNotifications->count() }}</span>
                     @endif
                 </a>
                 <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown" style="width: 380px; max-width: calc(100vw - 30px);">
                     <li>
                         <div class="dropdown-title">
                             You have <span
                                 id="notif-title-count">{{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }}</span>
                             new notification(s)
                         </div>
                     </li>
                     <li>
                         <div class="notif-scroll scrollbar-outer">
                             <div class="notif-center" id="notif-list-container">
                                 @if (auth()->check())
                                     @forelse(auth()->user()->notifications()->latest()->take(7)->get() as $notification)
                                         <a href="{{ $notification->data['url'] ?? '#' }}"
                                             class="notif-item {{ $notification->unread() ? 'unread-bg' : '' }}">
                                             <div
                                                 class="notif-icon {{ $notification->data['color'] ?? 'notif-primary' }}">
                                                 <i class="{{ $notification->data['icon'] ?? 'fa fa-bell' }}"></i>
                                             </div>
                                             <div class="notif-content">
                                                 <span class="block">
                                                     {{ $notification->data['message'] ?? 'New Notification' }}
                                                     @if ($notification->unread())
                                                         <span class="badge badge-xs badge-primary ms-1">New</span>
                                                     @endif
                                                 </span>
                                                 <span
                                                     class="time">{{ $notification->created_at->diffForHumans() }}</span>
                                             </div>
                                         </a>
                                     @empty
                                         <a href="#">
                                             <div class="notif-content text-center py-3 w-100">
                                                 <span class="block text-muted">No notifications found</span>
                                             </div>
                                         </a>
                                     @endforelse
                                 @endif
                             </div>
                         </div>
                     </li>
                 </ul>
             </li>

             @push('scripts')
                 <script>
                     function markAllNotificationsAsRead() {
                         let countElement = document.getElementById('notif-count');
                         if (countElement) {
                             $.post("{{ route('notifications.read-all') }}", {
                                 _token: "{{ csrf_token() }}"
                             }, function(response) {
                                 if (response.success) {
                                     $('#notif-count').remove();
                                     $('#notif-title-count').text('0');
                                     // Optional: Update text to "No new notifications" after delay
                                 }
                             });
                         }
                     }

                     $(document).ready(function() {
                         let searchTimeout;

                         $('#globalProjectSearch').on('keyup', function() {
                             clearTimeout(searchTimeout);
                             let query = $(this).val();
                             let $resultsBox = $('#globalProjectSearchResults');

                             if (query.length < 2) {
                                 $resultsBox.hide();
                                 return;
                             }

                             searchTimeout = setTimeout(function() {
                                 $.get("{{ route('project.search') }}?q=" + encodeURIComponent(query), function(
                                     data) {
                                     $resultsBox.empty();
                                     if (data.length > 0) {
                                         data.forEach(function(item) {
                                             $resultsBox.append(
                                                 `<li><a class="dropdown-item py-2" style="cursor:pointer;" href="{{ route('project.index') }}?show_id=${item.encrypted_id}"><i class="fas fa-project-diagram me-2 text-primary"></i> ${item.name}</a></li>`
                                             );
                                         });
                                         $resultsBox.show();
                                     } else {
                                         $resultsBox.append(
                                             '<li><span class="dropdown-item py-2 text-muted">No projects found</span></li>'
                                         );
                                         $resultsBox.show();
                                     }
                                 });
                             }, 300);
                         });

                         $(document).on('click', function(e) {
                             if (!$(e.target).closest('.nav-search').length) {
                                 $('#globalProjectSearchResults').hide();
                             }
                         });
                     });
                 </script>
             @endpush
             <li class="nav-item topbar-icon dropdown hidden-caret">
                 <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                     <i class="fas fa-layer-group"></i>
                 </a>
                 <div class="dropdown-menu quick-actions animated fadeIn">
                     <div class="quick-actions-header">
                         <span class="title mb-1">Quick Actions</span>
                         <span class="subtitle op-7">Shortcuts</span>
                     </div>
                      <div class="quick-actions-scroll scrollbar-outer">
                        <div class="quick-actions-items">
                            <div class="row m-0">
                                @if(auth()->user()->role == 'pm')
                                    <a class="col-6 col-md-4 p-0" href="{{ route('project.create') }}">
                                        <div class="quick-actions-item">
                                            <div class="avatar-item bg-primary rounded-circle">
                                                <i class="fas fa-plus-circle"></i>
                                            </div>
                                            <span class="text">New Project</span>
                                        </div>
                                    </a>
                                @endif

                                @if(auth()->user()->role != 'client' && auth()->user()->role != 'owner')
                                    <a class="col-6 col-md-4 p-0" href="{{ route('task.create') }}">
                                        <div class="quick-actions-item">
                                            <div class="avatar-item bg-info rounded-circle">
                                                <i class="fas fa-tasks"></i>
                                            </div>
                                            <span class="text">Create Task</span>
                                        </div>
                                    </a>
                                @endif

                                @if(auth()->user()->role == 'pm' || auth()->user()->role == 'owner')
                                    <a class="col-6 col-md-4 p-0" href="{{ route('report.index') }}">
                                        <div class="quick-actions-item">
                                            <div class="avatar-item bg-secondary rounded-circle">
                                                <i class="fas fa-file-invoice"></i>
                                            </div>
                                            <span class="text">Reports</span>
                                        </div>
                                    </a>
                                    <a class="col-6 col-md-4 p-0" href="{{ route('user.index') }}">
                                        <div class="quick-actions-item">
                                            <div class="avatar-item bg-danger rounded-circle">
                                                <i class="fas fa-users-cog"></i>
                                            </div>
                                            <span class="text">Users Management</span>
                                        </div>
                                    </a>
                                @endif

                                <a class="col-6 col-md-4 p-0" href="{{ route('profile.index') }}">
                                    <div class="quick-actions-item">
                                        <div class="avatar-item bg-warning rounded-circle">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <span class="text">My Profile</span>
                                    </div>
                                </a>

                                @if(auth()->user()->role == 'client' || auth()->user()->role == 'developer')
                                    <a class="col-6 col-md-4 p-0" href="{{ route('project.index') }}">
                                        <div class="quick-actions-item">
                                            <div class="avatar-item bg-primary rounded-circle">
                                                <i class="fas fa-project-diagram"></i>
                                            </div>
                                            <span class="text">My Projects</span>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                 </div>
             </li>

             <li class="nav-item topbar-user dropdown hidden-caret">
                 <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                     aria-expanded="false">
                     <div class="avatar-sm">
                         <img src="{{ auth()->user()->getAvatarUrl() }}" alt="..."
                             class="avatar-img rounded-circle" />
                     </div>
                     <span class="profile-username">
                         <span class="op-7">Hi,</span>
                         <span class="fw-bold">{{ auth()->user()->name }}</span>
                     </span>
                 </a>
                 <ul class="dropdown-menu dropdown-user animated fadeIn">
                     <div class="dropdown-user-scroll scrollbar-outer">
                         <li>
                             <div class="user-box">
                                 <div class="avatar-lg">
                                     <img src="{{ auth()->user()->getAvatarUrl() }}" alt="image profile"
                                         class="avatar-img rounded" />
                                 </div>
                                 <div class="u-text">
                                     <h4>{{ auth()->user()->name }}</h4>
                                     <p class="text-muted text-break mb-1">{{ auth()->user()->email }}</p>
                                     <span
                                         class="badge badge-secondary me-1">{{ ucfirst(auth()->user()->role) }}</span>
                                     <span id="network-status-badge" class="badge badge-success">Online</span>
                                 </div>
                             </div>
                         </li>
                         <li>
                             <div class="dropdown-divider"></div>
                             <a class="dropdown-item" href="{{ route('profile.index') }}">My Profile</a>
                             <div class="dropdown-divider"></div>
                             <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                 style="display: none;">
                                 @csrf
                             </form>
                             <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                 Logout
                             </a>
                         </li>
                     </div>
                 </ul>
             </li>
         </ul>
     </div>
 </nav>
 <!-- End Navbar -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badge = document.getElementById('network-status-badge');
        if (badge) {
            function updateNetworkStatus() {
                if (navigator.onLine) {
                    badge.textContent = 'Online';
                    badge.className = 'badge badge-success';
                } else {
                    badge.textContent = 'Offline';
                    badge.className = 'badge badge-danger';
                }
            }
            window.addEventListener('online', updateNetworkStatus);
            window.addEventListener('offline', updateNetworkStatus);
            updateNetworkStatus(); // Initial check
        }
    });
</script>
