<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8"/>
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="Woopi"/>
    <meta name="keywords" content=""/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="robots" content="index, follow"/>
    <meta name="theme-color" content="#ffffff">

    @include('admin_panel.layouts.partials.head-css')
    <script src="{{asset('assets/js/config.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>

<body>

<!-- START Wrapper -->
<div class="app-wrapper">

    <!-- Topbar Start -->
    @include('admin_panel.layouts.partials.topbar')
    <!-- Topbar End -->

    <!-- App Menu Start -->
    @include('admin_panel.layouts.partials.sidebar')



    <!-- ==================================================== -->
    <!-- Start right Content here -->
    <!-- ==================================================== -->
    <div class="page-content">

        <!-- Start Container Fluid -->
        <div class="container-fluid">

            <!-- Mensajes Flash Globales -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('custom-error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('custom-error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
        <!-- End Container Fluid -->

        <!-- Footer Start -->
        @include('admin_panel.layouts.partials.footer')
        <!-- Footer End -->

    </div>
    <!-- ==================================================== -->
    <!-- End Page Content -->
    <!-- ==================================================== -->

</div>
<!-- END Wrapper -->


<!-- Vendor Javascript -->
<script src="{{asset('assets/js/vendor.min.js')}}"></script>

<!-- App Javascript -->
<script src="{{asset('assets/js/app.js')}}"></script>

@yield('page-specific-scripts')


@foreach(config('template.plugins', []) as $plugin)
    @continue(empty($plugin['active']))
    @foreach(($plugin['files'] ?? []) as $file)
        @if(($file['type'] ?? '') === 'js')
            @php
                $href = ($file['asset'] ?? false)
                    ? asset('assets/' . ltrim($file['location'], '/'))
                    : $file['location'];
            @endphp
            <script src="{{$href}}"></script>
        @endif
    @endforeach
@endforeach

@stack('scripts')


</body>

</html>
