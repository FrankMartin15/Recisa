<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RECISA | Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="{{asset('assets/img/escudo.png') }}">
    <style>
        :root {
            --primary-color: #00476D;
            --secondary-color: #0066A1;
            --accent-color: #39d6d6;
            --light-blue: #e8f4f8;
            --white: #ffffff;
            --shadow: rgba(0, 71, 109, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(57, 214, 214, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(232, 244, 248, 0.4) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 0.8s ease-out;
        }
        
        .health-center-title {
            color: white;
            font-size: 2.8rem;
            font-weight: 800;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff, #e8f4f8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }
        
        .health-center-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
            border-radius: 2px;
            box-shadow: 0 2px 15px rgba(57, 214, 214, 0.6);
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(0, 71, 109, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.2s both;
            border: none;
        }
        
        .image-side {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            position: relative;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset("assets/img/dogs/RedDeSalud.png") }}');
            background-size: cover;
            background-position: center;
            opacity: 0.9;
            border-radius: 0;
        }
        
        .image-side::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(0, 71, 109, 0.2), rgba(0, 102, 161, 0.2));
        }
        
        .form-side {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 600px;
        }
        
        .login-title {
            color: var(--primary-color);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }
        
        .login-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-control {
            border: 2px solid rgba(0, 71, 109, 0.1);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 71, 109, 0.25);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-floating label {
            color: var(--primary-color);
            font-weight: 600;
            padding-left: 1rem;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.1rem;
            z-index: 10;
        }
        
        .remember-section {
            margin-bottom: 2rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid var(--primary-color);
            border-radius: 4px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-label {
            color: var(--primary-color);
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .login-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 71, 109, 0.4);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .login-btn:active {
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1.5rem;
            animation: slideInRight 0.5s ease-out;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        /* Medical icons overlay */
        .medical-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            color: rgba(255, 255, 255, 0.1);
            font-size: 3rem;
            z-index: 2;
        }
        
        .medical-overlay-2 {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: rgba(255, 255, 255, 0.1);
            font-size: 2rem;
            z-index: 2;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        
        @media (max-width: 768px) {
            .form-side {
                padding: 2rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .health-center-title {
                font-size: 2.2rem;
            }
            
            .login-card {
                margin: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .form-side {
                padding: 1.5rem 1rem;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .health-center-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Medical icons overlay -->
    <div class="medical-overlay">
        <i class="fas fa-stethoscope"></i>
    </div>
    <div class="medical-overlay-2">
        <i class="fas fa-heartbeat"></i>
    </div>
    
    <div class="container-fluid login-container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="header-section">
                    <h1 class="health-center-title">Puesto de Salud de Sausa - JAUJA</h1>
                </div>
                
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="image-side"></div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-side">
                                <h3 class="login-title">INICIO DE SESIÓN</h3>
                                
                                @if ($errors->any())
                                    @foreach ($errors->all() as $item)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-circle me-2"></i>{{$item}}
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endforeach
                                @endif
                                
                                <form method="post" action="{{url('login')}}" class="user">
                                    @csrf
                                    
                                    <div class="form-floating">
                                        <input class="form-control" maxlength="8" type="text" id="dni" placeholder="DNI" name="dni" value="{{ session('dni') ? session('dni') : '' }}" required>
                                        <label for="dni">DNI</label>
                                        <i class="fas fa-id-card input-icon"></i>
                                    </div>
                                    
                                    <div class="form-floating">
                                        <input class="form-control" type="password" id="password" placeholder="Contraseña" name="password" required>
                                        <label for="password">Contraseña</label>
                                        <i class="fas fa-lock input-icon"></i>
                                    </div>
                                    
                                    <div class="remember-section">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">
                                                Recordar sesión
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button class="btn login-btn w-100" type="submit">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Acceder
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $('#dni').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(function(registration) {
                    console.log('Service Worker registrado con éxito:', registration.scope);
                })
                .catch(function(error) {
                    console.log('Error al registrar el Service Worker:', error);
                });
        }
    </script>
    <script>
        window.addEventListener('online', () => {
            console.log('Conexión restaurada. Solicitando nuevo token CSRF...');
            fetch('/csrf-token')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener el token CSRF');
                    }
                    return response.json();
                })
                .then(data => {
                    const csrfMetaTag = document.querySelector('meta[name="csrf-token"]');
                    if (csrfMetaTag) {
                        csrfMetaTag.setAttribute('content', data.token);
                    }

                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.token;
                    });

                    console.log('Token CSRF actualizado:', data.token);
                })
                .catch(error => {
                    console.error('Error al actualizar el token CSRF:', error);
                });
        });
    </script>
</body>

</html>