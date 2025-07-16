@extends('layouts.app')
@section('title', 'Doctor')

@push('css')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
<style>
    /* DOCTOR DASHBOARD SPECIFIC STYLES */
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
        min-height: 180px;
        padding: 1.5rem !important;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    
    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        background-color: rgba(255,255,255,0.2) !important;
    }
    
    .progress-modern {
        height: 12px;
        border-radius: 10px;
        background-color: rgba(255,255,255,0.3);
    }
    
    .progress-bar-modern {
        border-radius: 10px;
        transition: width 0.8s ease;
        background-color: #ffffff !important;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }
    
    .stat-description {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        opacity: 0.8;
    }
    
    .row.g-4 {
        display: flex;
        flex-wrap: wrap;
    }
    
    .row.g-4 > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    
    .text-white-50 {
        color: #ffffff !important;
        font-size: 18px;
    }
    
    .gradient-primary {
        background: #902d41;
    }
    
    .gradient-success {
        background: #679436;
    }
    
    .gradient-info {
        background: #39d6d6;
    }
    
    .gradient-warning {
        background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
    }
    
    .dashboard-header {
        background: #00476D;
        color: white;
        border-radius: 15px;
        margin-bottom: 2rem;
    }

    /* Custom animations */
    .dashboard-card {
        animation: fadeInUp 0.6s ease-out;
        opacity: 0;
        animation-fill-mode: forwards;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
    .dashboard-card:nth-child(5) { animation-delay: 0.5s; }
    .dashboard-card:nth-child(6) { animation-delay: 0.6s; }

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

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-header h2 {
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .dashboard-card .card-body {
            min-height: 160px;
            padding: 1.25rem !important;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    }

    /* Especialización badge */
    .specialization-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 0.5rem;
    }

    /* Porcentaje en progreso */
    .progress-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    .progress-container {
        position: relative;
    }
</style>
@endpush

@section('content')
<!-- Header del Dashboard Doctor -->
<div class="dashboard-header p-4 mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="mb-1 fw-bold">Dashboard Médico</h2>
            <p class="mb-0 opacity-75">Panel de control para gestión de consultas</p>
        </div>
        <div class="col-auto">
            <i class="fas fa-user-md fa-3x opacity-50"></i>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row g-4">
        @foreach ($assignments as $assignment)
            <!-- Card de Citas por Especialización -->
            <div class="col-md-6 col-xl-4">
                <div class="card dashboard-card shadow-sm gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center h-100">
                            <div class="col">
                                <div class="specialization-badge">
                                    {{$assignment->specialization->name}}
                                </div>
                                <p class="stat-label text-white-50 text-uppercase mb-2">
                                    Citas Pendientes
                                </p>
                                <h3 class="stat-number text-white">{{ $assignment->appointment_pending_count }}</h3>
                                <small class="stat-description text-white-50">
                                    <i class="fas fa-clock me-1"></i>Por atender
                                </small>
                            </div>
                            <div class="col-auto">
                                <div class="card-icon">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Cupos por Especialización -->
            <div class="col-md-6 col-xl-4">
                <div class="card dashboard-card shadow-sm gradient-success text-white">
                    <div class="card-body">
                        <div class="row align-items-center h-100">
                            <div class="col">
                                <div class="specialization-badge">
                                    {{$assignment->specialization->name}}
                                </div>
                                <p class="stat-label text-white-50 text-uppercase mb-2">
                                    Cupos Disponibles
                                </p>
                                <h3 class="stat-number text-white">{{ $assignment->cupo_doctor}}</h3>
                                <small class="stat-description text-white-50">
                                    <i class="fas fa-calendar-plus me-1"></i>Disponibles hoy
                                </small>
                            </div>
                            <div class="col-auto">
                                <div class="card-icon">
                                    <i class="fas fa-ticket-alt text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach ($atendidos as $atendido)
            @php
                $denominator = $atendido->cupo_doctor + $atendido->appointment_count;
                if ($denominator > 0) {
                    $maxquatity = (($atendido->appointment_pending_count + $atendido->appointment_cancel_count) / $denominator) * 100;
                    $maxquatity = max(0, min(100, $maxquatity)); // Asegurar que esté entre 0-100
                } else {
                    $maxquatity = 0; // O el valor que consideres adecuado
                }
            @endphp
            <!-- Card de Atendidos con Progreso -->
            <div class="col-md-6 col-xl-4">
                <div class="card dashboard-card shadow-sm gradient-info text-white">
                    <div class="card-body">
                        <div class="row align-items-center h-100">
                            <div class="col">
                                <div class="specialization-badge">
                                    {{$atendido->specialization->name}}
                                </div>
                                <p class="stat-label text-white-50 text-uppercase mb-2">
                                    Pacientes Atendidos
                                </p>
                                <h3 class="stat-number text-white">{{$atendido->appointment_pending_count}}</h3>
                                
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-white-50">Progreso del día</small>
                                        <small class="text-white fw-bold">{{round($maxquatity)}}%</small>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress progress-modern">
                                            <div class="progress-bar progress-bar-modern" 
                                                 role="progressbar" 
                                                 style="width: {{$maxquatity}}%;"
                                                 aria-valuenow="{{$maxquatity}}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="card-icon">
                                    <i class="fas fa-user-check text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animación para las barras de progreso
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 800);
    });
    
    // Efecto de conteo para los números
    const numbers = document.querySelectorAll('.stat-number');
    numbers.forEach(number => {
        const target = parseInt(number.textContent);
        if (!isNaN(target) && target > 0) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                number.textContent = Math.floor(current);
            }, 50);
        }
    });

    // Efecto hover para las tarjetas
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
});
</script>
@endpush