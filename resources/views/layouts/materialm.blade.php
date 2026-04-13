<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MaterialM') }} - @yield('title')</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/materialm/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/materialm/css/styles.min.css') }}" />

    @stack('styles')
</head>

<body>
    <!-- Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        @yield('content')
    </div>

    <script src="{{ asset('assets/materialm/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/materialm/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    @stack('scripts')
</body>

</html>
