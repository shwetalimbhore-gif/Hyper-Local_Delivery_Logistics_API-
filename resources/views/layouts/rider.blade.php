<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rider Dashboard') - HyperLocal Delivery</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
    @stack('styles')
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="{{ route('rider.dashboard') }}" class="text-nowrap logo-img">
                        <img src="{{ asset('assets/images/logos/logo.svg') }}" alt="Logo" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>

                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <li class="nav-small-cap">
                            <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
                            <span class="hide-menu">Navigation</span>
                        </li>

                        <!-- Dashboard -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('rider.dashboard') ? 'active' : '' }}"
                               href="{{ route('rider.dashboard') }}">
                                <iconify-icon icon="solar:atom-line-duotone"></iconify-icon>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>

                        <!-- My Parcels -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('rider.parcels.*') ? 'active' : '' }}"
                               href="{{ route('rider.parcels.index') }}">
                                <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                                <span class="hide-menu">My Parcels</span>
                            </a>
                        </li>

                        <!-- Earnings -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('rider.earnings') ? 'active' : '' }}"
                               href="{{ route('rider.earnings') }}">
                                <iconify-icon icon="solar:wallet-money-line-duotone"></iconify-icon>
                                <span class="hide-menu">My Earnings</span>
                            </a>
                        </li>

                        <!-- Profile -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('rider.profile') ? 'active' : '' }}"
                               href="{{ route('rider.profile') }}">
                                <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                                <span class="hide-menu">My Profile</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- Sidebar End -->

        <!-- Main wrapper -->
        <div class="body-wrapper">
            <!-- Header Start -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        </li>
                    </ul>

                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">

                            <!-- Notifications Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="javascript:void(0)" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
                                    @if($unreadCount = Auth::user()->notifications()->where('is_read', false)->count())
                                        <span class="notification bg-danger rounded-circle">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="notificationDropdown" style="width: 350px;">
                                    <div class="message-body">
                                        <div class="px-3 py-2 border-bottom">
                                            <h6 class="mb-0">Notifications</h6>
                                        </div>
                                        <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                            @php
                                                $notifications = Auth::user()->notifications()->latest()->limit(10)->get();
                                            @endphp
                                            @forelse($notifications as $notification)
                                                <a href="javascript:void(0)" class="dropdown-item notification-item {{ !$notification->is_read ? 'bg-light' : '' }}" data-id="{{ $notification->id }}">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="flex-shrink-0">
                                                            @if($notification->type == 'success')
                                                                <iconify-icon icon="solar:check-circle-bold" class="fs-5 text-success"></iconify-icon>
                                                            @elseif($notification->type == 'warning')
                                                                <iconify-icon icon="solar:bell-bold" class="fs-5 text-warning"></iconify-icon>
                                                            @elseif($notification->type == 'error')
                                                                <iconify-icon icon="solar:danger-circle-bold" class="fs-5 text-danger"></iconify-icon>
                                                            @else
                                                                <iconify-icon icon="solar:info-circle-bold" class="fs-5 text-info"></iconify-icon>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fs-3 fw-medium">{{ $notification->title }}</p>
                                                            <small class="text-muted">{{ $notification->message }}</small>
                                                            <br>
                                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                        </div>
                                                    </div>
                                                </a>
                                            @empty
                                                <div class="text-center py-4">
                                                    <iconify-icon icon="solar:bell-linear" class="fs-1 text-muted"></iconify-icon>
                                                    <p class="mt-2 text-muted">No notifications</p>
                                                </div>
                                            @endforelse
                                        </div>
                                        @if(Auth::user()->notifications()->count() > 0)
                                            <div class="border-top text-center py-2">
                                                <a href="javascript:void(0)" id="markAllRead" class="small">Mark all as read</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </li>

                            <!-- User Profile -->
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : asset('assets/images/profile/user-1.jpg') }}"
                                         alt="" width="35" height="35" class="rounded-circle">
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                                    <div class="message-body">
                                        <div class="px-3 py-2 border-bottom">
                                            <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                            <small class="text-muted">{{ Auth::user()->email }}</small>
                                            <br>
                                            <small class="text-muted">ID: {{ Auth::user()->rider->employee_id ?? 'N/A' }}</small>
                                        </div>
                                        <a href="{{ route('rider.profile') }}" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-user fs-6"></i>
                                            <p class="mb-0 fs-3">My Profile</p>
                                        </a>
                                        <a href="{{ route('rider.earnings') }}" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-wallet fs-6"></i>
                                            <p class="mb-0 fs-3">Earnings</p>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('logout') }}" class="d-block">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                <i class="ti ti-logout fs-6"></i>
                                                <p class="mb-0 fs-3">Logout</p>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Header End -->

            <!-- Page Content -->
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <!-- Page Content End -->

            <!-- Footer -->
            <div class="py-6 px-6 text-center">
                <p class="mb-0 fs-4">© {{ date('Y') }} HyperLocal Delivery System. All rights reserved.</p>
            </div>
        </div>
        <!-- Main wrapper End -->
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    @push('scripts')
        <script>
            // Mark single notification as read
            $('.notification-item').click(function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('rider.notification.read') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function() {
                        location.reload();
                    }
                });
            });

            // Mark all notifications as read
            $('#markAllRead').click(function() {
                $.ajax({
                    url: "{{ route('rider.notifications.read-all') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        location.reload();
                    }
                });
            });
        </script>
    @endpush

    @stack('scripts')
</body>

</html>
