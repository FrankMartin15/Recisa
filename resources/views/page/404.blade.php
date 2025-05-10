<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <link rel="icon" href="{{ asset('assets/img/escudo.png') }}">
        <title>404 Error - Sistema CIMEXA</title>
        <link href="{{asset('assets/css/template.css')}}" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .card-custom {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                padding: 30px;
                background-color: #ffffff;
            }
            .text-shadow {
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            }
            .img-error {
                max-width: 100%;
                height: auto;
            }
        </style>
    </head>
    <body>
        <div id="layoutError" class="d-flex align-items-center justify-content-center vh-100 bg-light">
            <div class="card-custom text-center">
                <img class="mb-4 img-error" src="{{asset('assets/img/error-404-monochrome.svg')}}" alt="404 Error" />
                <h1 class="display-4 text-shadow">404</h1>
                <p class="lead text-shadow">Esta URL solicitada no se encontró en este servidor.</p>
                @if(Auth::check())
                    @switch(Auth::user()->user_level)
                        @case(1)
                            <a href="{{url('admin/dashboard')}}" class="btn btn-primary mt-3">
                                <i class="fas fa-arrow-left me-1"></i> Volver al panel
                            </a>
                            @break
                        @case(2)
                            <a href="{{url('secretary/dashboard')}}" class="btn btn-primary mt-3">
                                <i class="fas fa-arrow-left me-1"></i> Volver al panel
                            </a>
                            @break
                        @case(3)
                            <a href="{{url('doctor/dashboard')}}" class="btn btn-primary mt-3">
                                <i class="fas fa-arrow-left me-1"></i> Volver al panel
                            </a>
                            @break
                    @endswitch
                @endif
            </div>
        </div>
        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/scripts.js')}}"></script>
    </body>
</html>