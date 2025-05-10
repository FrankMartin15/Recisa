<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>401 Error - No autorizado</title>
        <link href="{{asset('assets/css/template.css')}}" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .card-custom {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                padding: 20px;
            }
            .text-shadow {
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            }
        </style>
    </head>
    <body>
        <div id="layoutError" class="d-flex align-items-center justify-content-center vh-100">
            <div class="card-custom text-center bg-light">
                <h1 class="display-1 text-shadow">401</h1>
                <p class="lead text-shadow">No Autorizado</p>
                <p class="text-shadow">El acceso al sistema está denegado</p>
                <a href="{{url('')}}" class="btn btn-primary mt-3">
                    <svg style="width: 20px; height: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                    </svg>
                    Regresar al Login
                </a>
            </div>
        </div>
        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/scripts.js')}}"></script>
    </body>
</html>