<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>500 Error - Servidor</title>
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
        </style>
    </head>
    <body>
        <div id="layoutError" class="d-flex align-items-center justify-content-center vh-100 bg-light">
            <div class="card-custom text-center">
                <h1 class="display-1 text-shadow">500</h1>
                <p class="lead text-shadow">Error interno del servidor.</p>
                <a href="{{url('/')}}" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-1"></i> Regresar al Login
                </a>
            </div>
        </div>
        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/scripts.js')}}"></script>
    </body>
</html>