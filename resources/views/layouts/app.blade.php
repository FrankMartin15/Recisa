<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> RECISA| @yield('title')</title>
    <link rel="icon" href="{{ asset('assets/img/escudo.png') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
    
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
    
    {{-- 🔧 SERVICE WORKER ÚNICO Y SIMPLIFICADO --}}
    <script>
    // ⭐ REGISTRO DEL SERVICE WORKER CORRECTO
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/service-worker.js', {
                scope: '/'
            })
            .then(function(registration) {
                console.log('✅ Service Worker registrado correctamente:', registration.scope);
                
                // Manejar actualizaciones del SW de forma simple
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('🔄 Nueva versión del Service Worker disponible');
                                // Recargar automáticamente después de 3 segundos
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        });
                    }
                });
            })
            .catch(function(error) {
                console.warn('⚠️ Error al registrar Service Worker:', error);
                // No es crítico, la app puede funcionar sin SW
            });
        });

        // Escuchar mensajes del Service Worker (simplificado)
        navigator.serviceWorker.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'RELOAD_PAGE') {
                window.location.reload();
            }
        });
    } else {
        console.info('ℹ️ Service Workers no soportados en este navegador');
    }

    // ⭐ CSRF TOKEN REFRESH SIMPLIFICADO
    window.addEventListener('online', () => {
        console.log('🌐 Conexión restaurada, actualizando CSRF token...');
        
        // Solo intentar si realmente hay conexión
        fetch('/csrf-token', {
            method: 'GET',
            cache: 'no-cache',
            credentials: 'same-origin'
        })
        .then(response => response.ok ? response.json() : null)
        .then(data => {
            if (data && data.token) {
                // Actualizar meta tag
                const csrfMetaTag = document.querySelector('meta[name="csrf-token"]');
                if (csrfMetaTag) {
                    csrfMetaTag.setAttribute('content', data.token);
                }

                // Actualizar inputs de formularios
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = data.token;
                });

                console.log('✅ Token CSRF actualizado');
            }
        })
        .catch(error => {
            console.log('⚠️ No se pudo actualizar el token CSRF:', error.message);
        });
    });

    // ⭐ VARIABLE DE ESTADO DEL SERVIDOR (simplificada)
    window.serverOnline = @json($serverOnline ?? true);
    </script>
</body>

</html>