@extends('layouts.app')
@section('title', 'Secretaria')

@push('css')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
<style>
    /* SECRETARY DASHBOARD SPECIFIC STYLES */
    .dashboard-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .dashboard-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 200px;
        padding: 2rem !important;
    }
    
    .dashboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
    }
    
    .card-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
        background-color: rgba(255,255,255,0.25) !important;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 3.5rem;
        font-weight: 800;
        margin: 0;
        line-height: 1;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-label {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    .stat-description {
        font-size: 0.875rem;
        margin-top: 1rem;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .row.g-4 {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .row.g-4 > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    
    .text-white-50 {
        color: rgba(255,255,255,0.8) !important;
        font-size: 18px;
    }
    
    .gradient-primary {
        background: #0093c8;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .gradient-success {
        background: #822943;
        box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
    }
    
    .dashboard-header {
        background: #00476D;
        color: white;
        border-radius: 20px;
        margin-bottom: 3rem;
    }

    /* Custom animations */
    .dashboard-card {
        animation: fadeInUp 0.8s ease-out;
        opacity: 0;
        animation-fill-mode: forwards;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.4s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-header h2 {
            font-size: 1.75rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .dashboard-card .card-body {
            min-height: 180px;
            padding: 1.5rem !important;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }
    }

    /* Efectos especiales para secretaria */
    .secretary-highlight {
        position: relative;
        overflow: hidden;
    }

    .secretary-highlight::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.5s ease;
        opacity: 0;
    }

    .secretary-highlight:hover::before {
        animation: shine 0.8s ease-in-out;
    }

    @keyframes shine {
        0% {
            opacity: 0;
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        50% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    /* Pulse effect for numbers */
    .stat-number {
        animation: pulse 2s ease-in-out infinite alternate;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        100% {
            transform: scale(1.02);
        }
    }

    /* Icon animations */
    .card-icon i {
        transition: all 0.3s ease;
    }

    .dashboard-card:hover .card-icon i {
        transform: scale(1.1) rotate(5deg);
    }

    /* Welcome section */
    .welcome-section {
        background: #s;
        border-radius: 20px;
        margin-top: 2rem;
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 1rem;
    }

    .feature-card {
        text-align: center;
        padding: 1.5rem;
        border-radius: 15px;
        background: white;
        transition: all 0.3s ease;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .icon-primary { background: #ff5400 }
    .icon-success { background: #822943 }
    .icon-info { background: #7dbd00 }
    .icon-warning { background: #ffcc00}
</style>
@endpush

@section('content')
<!-- Header del Dashboard Secretaria -->
<div class="dashboard-header p-4 mb-5">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="mb-1 fw-bold">Dashboard de Secretaría</h2>
            <p class="mb-0 opacity-75">Centro de control administrativo y gestión de pacientes</p>
        </div>
        <div class="col-auto">
            <i class="fas fa-clipboard-user fa-3x opacity-50"></i>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Tarjetas principales más grandes y centradas -->
    <div class="row g-5 justify-content-center">
        <!-- Card de Doctores -->
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card dashboard-card secretary-highlight shadow-lg gradient-primary text-white">
                <div class="card-body text-center">
                    <div class="card-icon mx-auto">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <p class="stat-label text-white-50 text-uppercase">
                        Doctores Registrados
                    </p>
                    <h3 class="stat-number text-white mb-3">{{$doctor}}</h3>
                    <small class="stat-description text-white-50">
                        <i class="fas fa-stethoscope me-2"></i>Personal médico disponible
                    </small>
                </div>
            </div>
        </div>

        <!-- Card de Pacientes -->
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card dashboard-card secretary-highlight shadow-lg gradient-success text-white">
                <div class="card-body text-center">
                    <div class="card-icon mx-auto">
                        <i class="fas fa-user-injured text-white"></i>
                    </div>
                    <p class="stat-label text-white-50 text-uppercase">
                        Citas Programadas
                    </p>
                    <h3 class="stat-number text-white mb-3">{{$appointment}}</h3>
                    <small class="stat-description text-white-50">
                        <i class="fas fa-calendar-check me-2"></i>Pacientes en agenda
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de herramientas de trabajo -->
    <div class="welcome-section p-4 mt-5">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="mb-1 fw-bold text-dark">
                    <i class="fas fa-tools text-warning me-2"></i>
                    Herramientas de Trabajo
                </h4>
                <p class="mb-0 text-muted">Accesos rápidos para gestión diaria</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-3">
                <a href="{{url('recisa/appoitnment/add')}}" class="text-decoration-none">
                    <div class="feature-card">
                        <div class="feature-icon icon-primary mx-auto">
                            <i class="fas fa-calendar-plus text-white"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Registrar Cita</h6>
                        <p class="text-muted small mb-0">Programar nuevas citas médicas</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <a href="{{url('recisa/patients/add')}}" class="text-decoration-none">
                    <div class="feature-card">
                        <div class="feature-icon icon-success mx-auto">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Registrar Paciente</h6>
                        <p class="text-muted small mb-0">Agregar nuevos pacientes</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <a href="{{url('secretary/reporte/cita')}}" class="text-decoration-none">
                    <div class="feature-card">
                        <div class="feature-icon icon-info mx-auto">
                            <i class="fas fa-file-medical-alt text-white"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Reporte de Citas</h6>
                        <p class="text-muted small mb-0">Generar reportes de citas</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <a href="{{url('recisa/clinicalhistories/created')}}" class="text-decoration-none">
                    <div class="feature-card">
                        <div class="feature-icon icon-warning mx-auto">
                            <i class="fas fa-notes-medical text-white"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Historial Clínico</h6>
                        <p class="text-muted small mb-0">Gestionar historiales médicos</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Efecto de conteo para los números (más lento y dramático)
    const numbers = document.querySelectorAll('.stat-number');
    numbers.forEach(number => {
        const target = parseInt(number.textContent);
        if (!isNaN(target) && target > 0) {
            let current = 0;
            const increment = target / 60; // Más lento
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                number.textContent = Math.floor(current);
            }, 40);
        }
    });

    // Efectos hover mejorados para las tarjetas principales
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '20';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // Efectos para las feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.feature-icon');
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(10deg)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.feature-icon');
            if (icon) {
                icon.style.transform = 'scale(1) rotate(0deg)';
            }
        });
    });

    // Animación especial de bienvenida
    setTimeout(() => {
        const header = document.querySelector('.dashboard-header');
        if (header) {
            header.style.animation = 'pulse 1s ease-in-out';
        }
    }, 1000);
});
</script>
@endpush