@extends('layouts.app')
@section('title', 'Administrador')

@push('css')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
<style>
    /* DASHBOARD SPECIFIC STYLES */
    .dashboard-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        height: 100%; /* Esto asegura que todas las cards tengan la misma altura */
        display: flex;
        flex-direction: column;
    }
    
    .dashboard-card .card-body {
        flex: 1; /* Hace que el card-body se expanda para llenar el espacio disponible */
        display: flex;
        flex-direction: column;
        justify-content: center; /* Centra el contenido verticalmente */
        min-height: 180px; /* Altura mínima consistente */
        padding: 1.5rem !important;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    
    .card-icon {
        font-size: 60px !important;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0; /* Evita que el icono se comprima */
    }
    
    .progress-modern {
        height: 8px;
        border-radius: 10px;
        background-color: rgba(255,255,255,0.3);
    }
    
    .progress-bar-modern {
        border-radius: 10px;
        transition: width 0.6s ease;
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
    
    /* Asegurar que todas las columnas tengan la misma altura */
    .row.g-4 {
        display: flex;
        flex-wrap: wrap;
    }
    
    .row.g-4 > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    .text-white-50{
        color: #ffffff !important;
        font-size: 18px
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
        background: #ffb703;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #00476D 0%, #0066A1 100%);
        color: white;
        border-radius: 15px;
        margin-bottom: 2rem;
    }

    /* Quick Actions */
    .quick-actions .btn {
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .quick-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .quick-actions .btn i {
        font-size: 1.5rem;
    }

    /* Notificaciones de Doctores */
    .doctor-notification {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
        text-decoration: none;
        color: inherit;
    }

    .doctor-notification:hover {
        background-color: #f8f9fa;
        text-decoration: none;
        color: inherit;
    }

    .doctor-notification:last-child {
        border-bottom: none;
    }

    .dropdown-list-image {
        position: relative;
        width: 45px;
        height: 45px;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .img-profile {
        width: 45px;
        height: 45px;
        object-fit: cover;
    }

    .status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .doctor-info {
        flex: 1;
        min-width: 0;
    }

    .doctor-info .text-truncate {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .doctor-info .small {
        font-size: 0.75rem;
        color: #6c757d;
        margin: 0;
    }

    /* Citas próximas */
    .appointment-item {
        padding: 0.75rem;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }

    .appointment-item:hover {
        background-color: #f8f9fa;
    }

    .appointment-item:last-child {
        border-bottom: none;
    }

    .appointment-time {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
    }

    .appointment-patient {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .appointment-doctor {
        font-size: 0.75rem;
        color: #6c757d;
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

    /* Custom animations */
    .dashboard-card {
        animation: fadeInUp 0.6s ease-out;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.4s; }

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

    /* Altura fija para las secciones inferiores */
    .info-cards .card {
        height: 400px;
        display: flex;
        flex-direction: column;
    }

    .info-cards .card-body {
        flex: 1;
        overflow-y: auto;
    }

    .empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        height: 100%;
        color: #6c757d;
    }

    .empty-state i {
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<!-- Header del Dashboard -->
<div class="dashboard-header p-4 mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="mb-1 fw-bold">Dashboard Administrativo</h2>
            <p class="mb-0 opacity-75">Resumen general del sistema médico</p>
        </div>
        <div class="col-auto">
            <i class="fas fa-chart-line fa-3x opacity-50"></i>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="container-fluid">
    <div class="row g-4">
        <!-- Doctores -->
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center h-100">
                        <div class="col">
                            <p class="stat-label text-white-50 text-uppercase">
                                Doctores
                            </p>
                            <h3 class="stat-number text-white">{{$doctor ?? 0}}</h3>
                            <small class="stat-description text-white-50">
                                <i class="fas fa-arrow-up me-1"></i>Personal médico activo
                            </small>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-opacity-20">
                                <i class="fas fa-stethoscope text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pacientes -->
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm gradient-success text-white">
                <div class="card-body">
                    <div class="row align-items-center h-100">
                        <div class="col">
                            <p class="stat-label text-white-50 text-uppercase">
                                Pacientes Atendidos
                            </p>
                            <h3 class="stat-number text-white">{{$patient ?? 0}}</h3>
                            <small class="stat-description text-white-50">
                                <i class="fas fa-heart me-1"></i>En nuestro cuidado
                            </small>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-opacity-20">
                                <i class="fas fa-user-injured text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas -->
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center h-100">
                        <div class="col">
                            <p class="stat-label text-white-50 text-uppercase">
                                Citas Programadas
                            </p>
                            <h3 class="stat-number text-white">{{$appointment ?? 0}}</h3>
                            
                            @php
                                $percentage = isset($maxquatity) && $maxquatity != 0 ? 
                                    (($appointment ?? 0) / $maxquatity) * 100 : 0;
                            @endphp
                            
                            <div class="mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-white-50">Capacidad</small>
                                    <small class="text-white fw-bold">{{round($percentage)}}%</small>
                                </div>
                                <div class="progress progress-modern">
                                    <div class="progress-bar progress-bar-modern bg-white" 
                                         role="progressbar" 
                                         style="width: {{$percentage}}%;"
                                         aria-valuenow="{{$percentage}}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-opacity-20">
                                <i class="fas fa-calendar-check text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sesiones -->
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm gradient-warning text-white">
                <div class="card-body">
                    <div class="row align-items-center h-100">
                        <div class="col">
                            <p class="stat-label text-white-50 text-uppercase">
                                Sesiones Activas
                            </p>
                            <h3 class="stat-number text-white">18</h3>
                            <small class="stat-description text-white-50">
                                <i class="fas fa-clock me-1"></i>En tiempo real
                            </small>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-opacity-20">
                                <i class="fas fa-desktop text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección adicional con información rápida -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body quick-actions">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{url('admin/admin/add')}}" class="btn btn-outline-primary w-100 py-3 border-2">
                                <i class="fas fa-plus-circle mb-2 d-block"></i>
                                Nuevo Doctor
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{url('recisa/patients/add')}}" class="btn btn-outline-success w-100 py-3 border-2">
                                <i class="fas fa-user-plus mb-2 d-block"></i>
                                Registrar Paciente
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{url('recisa/appoitnment/add')}}" class="btn btn-outline-info w-100 py-3 border-2">
                                <i class="fas fa-calendar-plus mb-2 d-block"></i>
                                Nueva Cita
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{url('recisa/patients/list')}}"class="btn btn-outline-warning w-100 py-3 border-2">
                                <i class="fas fa-chart-bar mb-2 d-block"></i>
                                Generar Reporte de Paciente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas adicionales con altura fija -->
    <div class="row mt-4 info-cards">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-calendar-alt text-info me-2"></i>
                        Próximas Citas
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if (isset($appointments) && count($appointments) > 0)
                        @foreach ($appointments->take(5) as $appointment)
                            <div class="appointment-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="appointment-patient">
                                            {{$appointment->patient->names}} {{$appointment->patient->surnames}}
                                        </div>
                                        <div class="appointment-doctor">
                                            Dr. {{$appointment->doctor->user->names}} {{$appointment->doctor->user->surnames}}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="appointment-time">
                                            {{date('d/m', strtotime($appointment->date))}}
                                        </div>
                                        <div class="appointment-time">
                                            {{$appointment->time}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="p-3">
                            <a href="{{url('recisa/appoitnment/list')}}" class="btn btn-outline-info btn-sm w-100">Ver Todas las Citas</a>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-calendar-check fa-3x"></i>
                            <p class="mb-0">No hay citas programadas para hoy</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-md text-success me-2"></i>
                        Estado de Doctores
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if (isset($doctors) && count($doctors) > 0)
                        @foreach ($doctors as $doctor)
                            <a href="#" class="doctor-notification">
                                <div class="dropdown-list-image">
                                    @if ($doctor->image == null)
                                        <img class="border rounded-circle img-profile"
                                            src="https://i.postimg.cc/hjSBbZX4/doctor.png"
                                            alt="Doctor">
                                    @else
                                        <img class="border rounded-circle img-profile"
                                            src="{{ Storage::url('public/perfiles/' . $doctor->image) }}"
                                            onerror="this.src='https://i.postimg.cc/hjSBbZX4/doctor.png';"
                                            alt="Doctor">
                                    @endif
                                    @switch($doctor->user_status ?? 1)
                                        @case(0)
                                            <div class="bg-warning status-indicator"></div>
                                        @break
                                        @case(1)
                                            <div class="bg-success status-indicator"></div>
                                        @break
                                        @default
                                    @endswitch
                                </div>
                                <div class="doctor-info">
                                    <div class="text-truncate">
                                        <span>{{ $doctor->specialization_name ?? 'N/A' }} - </span>
                                        <span>{{ $doctor->user_name ?? 'N/A' }}</span>
                                    </div>
                                    <p class="small mb-0">Cupos disponibles: {{ $doctor->cupo_doctor ?? 0 }}</p>
                                </div>
                            </a>
                        @endforeach
                        <div class="p-3">
                            <a href="{{url('/admin/admin/list')}}" class="btn btn-outline-success btn-sm w-100">Ver Todos los Doctores</a>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-md fa-3x"></i>
                            <p class="mb-0">No hay doctores registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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
        }, 500);
    });
    
    // Efecto de conteo para los números
    const numbers = document.querySelectorAll('.stat-number');
    numbers.forEach(number => {
        const target = parseInt(number.textContent);
        if (!isNaN(target) && target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                number.textContent = Math.floor(current);
            }, 20);
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

    // Actualizar indicador de conexión
    function updateConnectionStatus() {
        const indicator = document.getElementById('connection-status');
        if (indicator) {
            const isOnline = navigator.onLine;
            indicator.style.background = isOnline ? '#28a745' : '#dc3545';
            indicator.title = isOnline ? 'Conectado' : 'Sin conexión';
        }
    }

    // Verificar estado de conexión
    updateConnectionStatus();
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
});
</script>
@endpush