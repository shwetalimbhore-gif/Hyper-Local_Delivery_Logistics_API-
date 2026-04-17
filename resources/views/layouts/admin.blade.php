<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - HyperLocal Delivery</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

    @stack('styles')
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="{{ route('admin.dashboard') }}" class="text-nowrap logo-img">
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
                            <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                               href="{{ route('admin.dashboard') }}">
                                <iconify-icon icon="solar:atom-line-duotone"></iconify-icon>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>

                            <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                                <span class="hide-menu">Parcels</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.parcels.index') }}">
                                        <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                                        <span class="hide-menu">All Parcels</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.parcels.trash') }}">
                                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                        <span class="hide-menu">Trash</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                            <!-- Riders Dropdown -->
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:bicycle-line-duotone"></iconify-icon>
                                <span class="hide-menu">Riders</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.riders.index') }}">
                                        <span class="hide-menu">All Riders</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.riders.trash') }}">
                                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                        <span class="hide-menu">Trash</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                            <!-- Hubs Dropdown -->
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:warehouse-line-duotone"></iconify-icon>
                                <span class="hide-menu">Hubs</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.hubs.index') }}">
                                        <span class="hide-menu">All Hubs</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.hubs.trash') }}">
                                        <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                        <span class="hide-menu">Trash</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <span class="sidebar-divider lg"></span>
                        </li>

                        <!-- Reports -->
                        <li class="nav-small-cap">
                            <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
                            <span class="hide-menu">Reports</span>
                        </li>

                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('admin.reports.earnings') ? 'active' : '' }}"
                            href="{{ route('admin.reports.earnings') }}">
                                <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                                <span class="hide-menu">Earnings</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->routeIs('admin.reports.delivery') ? 'active' : '' }}"
                            href="{{ route('admin.reports.delivery') }}">
                                <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
                                <span class="hide-menu">Delivery Reports</span>
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
                            <!-- Notifications Dropdown with Real-time Updates -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="javascript:void(0)" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 10px;">
                                    0
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="notificationDropdown" style="width: 380px;">
                                <div class="message-body">
                                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Notifications</h6>
                                        <button type="button" id="markAllReadBtn" class="btn btn-sm btn-link text-decoration-none">
                                            Mark all as read
                                        </button>
                                    </div>
                                    <div id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                            <!-- User Profile -->
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ asset('assets/images/profile/user-1.jpg') }}" alt="" width="35" height="35" class="rounded-circle">
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                                    <div class="message-body">
                                        <div class="px-3 py-2 border-bottom">
                                            <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                            <small class="text-muted">{{ Auth::user()->email }}</small>
                                        </div>
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-user fs-6"></i>
                                            <p class="mb-0 fs-3">My Profile</p>
                                        </a>
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-settings fs-6"></i>
                                            <p class="mb-0 fs-3">Settings</p>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script>
        // Fetch notifications
        function fetchNotifications() {
            $.ajax({
                url: "{{ route('admin.notifications.fetch') }}",
                method: 'GET',
                success: function(response) {
                    updateNotificationList(response.notifications);
                    updateNotificationBadge(response.unread_count);
                }
            });
        }

        // Update notification list
        function updateNotificationList(notifications) {
            let html = '';

            if (notifications.length === 0) {
                html = `
                    <div class="text-center py-4">
                        <iconify-icon icon="solar:bell-linear" class="fs-1 text-muted"></iconify-icon>
                        <p class="mt-2 text-muted">No notifications</p>
                    </div>
                `;
            } else {
                notifications.forEach(function(notification) {
                    let icon = getNotificationIcon(notification.type);
                    let bgClass = notification.is_read ? '' : 'bg-light';

                    html += `
                        <div class="dropdown-item notification-item ${bgClass}" data-id="${notification.id}" style="cursor: pointer; border-bottom: 1px solid #eee;">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    ${icon}
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-medium">${notification.title}</p>
                                    <small class="text-muted d-block">${notification.message}</small>
                                    <small class="text-muted">${notification.time_ago}</small>
                                </div>
                                ${!notification.is_read ? '<span class="badge bg-primary rounded-pill">New</span>' : ''}
                            </div>
                        </div>
                    `;
                });
            }

            $('#notificationList').html(html);

            // Add click event to mark as read
            $('.notification-item').click(function() {
                let id = $(this).data('id');
                markAsRead(id);
            });
        }

        // Update notification badge
        function updateNotificationBadge(count) {
            if (count > 0) {
                $('#notificationBadge').text(count).show();
            } else {
                $('#notificationBadge').hide();
            }
        }

        // Get notification icon based on type
        function getNotificationIcon(type) {
            switch(type) {
                case 'success':
                    return '<iconify-icon icon="solar:check-circle-bold" class="fs-5 text-success"></iconify-icon>';
                case 'warning':
                    return '<iconify-icon icon="solar:bell-bold" class="fs-5 text-warning"></iconify-icon>';
                case 'error':
                    return '<iconify-icon icon="solar:danger-circle-bold" class="fs-5 text-danger"></iconify-icon>';
                default:
                    return '<iconify-icon icon="solar:info-circle-bold" class="fs-5 text-info"></iconify-icon>';
            }
        }

        // Mark notification as read
        function markAsRead(id) {
            $.ajax({
                url: "{{ route('admin.notification.read') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function() {
                    fetchNotifications();
                }
            });
        }

        // Mark all as read
        $('#markAllReadBtn').click(function() {
            $.ajax({
                url: "{{ route('admin.notifications.read-all') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    fetchNotifications();
                }
            });
        });

        // Fetch notifications every 10 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 10000);
    </script>

    @stack('scripts')
</body>

</html>
