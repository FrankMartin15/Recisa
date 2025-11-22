<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> RECISA| @yield('title')</title>
    <link rel="icon" href="{{ asset('assets/img/escudo.png') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00476D">
    
    <!--Anderson-->
    <link rel="stylesheet" href="{{asset('assets/bootstrap/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
    <link rel="stylesheet" href="{{asset('assets/css/google-fonts-nunito.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/google-fonts-roboto.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/fontawesome-all.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/fontawesome5-overrides.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Billing-Table-with-Add-Row--Fixed-Header-Feature.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Data-Table-styles.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Data-Table.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Footer-Basic-icons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/FORM.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/dataTables.bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/theme.bootstrap_4.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/jquery-ui.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Ludens-Users---25-After-Register.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Pretty-Registration-Form-.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Register-form.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Responsive-Form-Contact-Form-Clean.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Responsive-Form.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Table-With-Search-search-table.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/Table-With-Search.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/lineicons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <!--Anderson-->

    <!--Menu Bar-->
    <link rel="stylesheet" href="{{asset('assets/css/menu_bar.css')}}">
    <script src="{{asset('assets/js/fontawesome-kit.js')}}"></script>
    <!--Menu Bar-->
    @stack('css')
</head>

<body id="page-top" style="--bs-primary: #00486E;--bs-primary-rgb: 0,72,110;">
    <div id="wrapper">
        @include('layouts.navigation-menu')
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                @include('layouts.navigation-header')
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    <script src="{{asset('assets/js/jquery-3.7.1.js')}}"></script>
    <!--Anderson-->
    <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/Billing-Table-with-Add-Row--Fixed-Header-Feature.js')}}"></script>
    <script src="{{asset('assets/js/jquery.tablesorter.js')}}"></script>
    <script src="{{asset('assets/js/widget-filter.min.js')}}"></script>
    <script src="{{asset('assets/js/widget-storage.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('assets/js/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.js')}}"></script>
    <script src="{{asset('assets/js/Table-With-Search.js')}}"></script>
    <script src="{{asset('assets/js/theme.js')}}"></script>
    <script src="{{ asset('assets/js/offline-manager.js') }}"></script>
    <!--Anderson-->

    <!--JS bar-->
    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/menu_bar.js')}}"></script>
    <!--JS bar-->
    
    <!--JS tablas-->
    <script src="{{asset('assets/js/dataTables.js')}}"></script>
    <script src="{{asset('assets/js/dataTables.bootstrap5.js')}}"></script>
    <!--JS tablas-->
    
    @stack('js')
    
    {{-- 🔧 SERVICE WORKER REGISTRATION --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('✅ Service Worker registrado:', reg.scope))
                    .catch(err => console.error('⚠️ Error SW:', err));
            });
        }
    </script>
</body>

</html>