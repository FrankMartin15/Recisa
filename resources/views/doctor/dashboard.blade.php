@extends('layouts.app')
@section('title', 'Doctor')

@push('css')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.css') }}">
<script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
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
                // Cálculo basado en las citas del día de hoy
                $totalCitasHoy = $atendido->appointment_today_count;
                $citasAtendidas = $atendido->appointment_attended_count;
                $citasNoAsistio = $atendido->appointment_noshow_count;
                $citasCompletadas = $citasAtendidas + $citasNoAsistio;
                
                if ($totalCitasHoy > 0) {
                    $maxquatity = ($citasCompletadas / $totalCitasHoy) * 100;
                    $maxquatity = max(0, min(100, $maxquatity)); // Asegurar que esté entre 0-100
                } else {
                    $maxquatity = 0; // Sin citas hoy = 0%
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
                                    Pacientes Con Citas Hoy
                                </p>
                                <h3 class="stat-number text-white">{{$citasCompletadas}}/{{$totalCitasHoy}}</h3>
                                
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

    <!-- Tabla de Citas con Filtros -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <p class="text-primary m-0 fw-bold">Mis Citas</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Fecha Inicio</label>
                                    <input type="date" id="fecha_inicio" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Fecha Fin</label>
                                    <input type="date" id="fecha_fin" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">&nbsp;</label>
                                    <button type="button" id="btn_filtrar" class="btn btn-primary btn-sm w-100" style="background-color: #00476D !important;">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table" role="grid">
                        <table id="especialidades" class="table my-0">
                            <thead>
                                <tr>
                                    <th style="width: 20px; font-weight:bold; text-align:center">#</th>
                                    <th style="width: 350px; font-weight:bold; text-align:center">Paciente</th>
                                    <th style="width: 150px; font-weight:bold; text-align:center">Especialidad</th>
                                    <th style="width: 100px; font-weight:bold; text-align:center">Fecha</th>
                                    <th style="width: 80px; font-weight:bold; text-align:center">Hora</th>
                                    <th style="width: 100px; font-weight:bold; text-align:center">Estado</th>
                                    <th style="font-weight:bold; text-align:center" class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_citas_body">
                                @foreach ($appointments as $value => $appointment)
                                    <tr>
                                        <td style="text-align: left">{{ $value + 1 }}</td>
                                        <td style="text-align: left">{{ $appointment->patient->names }} {{ $appointment->patient->surnames }}</td>
                                        <td style="text-align: left">{{ $appointment->doctor->specialization->name }}</td>
                                        <td style="text-align: left">{{ $appointment->date }}</td>
                                        <td style="text-align: left">{{ $appointment->time }}</td>
                                        <td style="text-align: center">
                                            @if($appointment->status == 0)
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @elseif($appointment->status == 1)
                                                <span class="badge bg-success">Atendido</span>
                                            @elseif($appointment->status == 2)
                                                <span class="badge bg-danger">No Asistió</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ url('doctor/attend/edit/'.$appointment->id) }}" class="btn btn-primary btn-sm" style="background: #F4D03F !important;">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// Inicializar DataTables
let table = $('#especialidades').DataTable({
    responsive: true,
    autoWidth:false,
    "language": {
        "lengthMenu": "Mostrar "+
                        `<select class="custom-select custom-select-sm w-50 form-select form-select-sm mb-2">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>`,
        "zeroRecords": "No se encontraron citas en el rango seleccionado",
        "info": "Mostrando la página _PAGE_ de _PAGES_ de _TOTAL_ citas",
        "infoEmpty": "No hay registros disponibles",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "search": "Buscar:",
        "emptyTable": "No hay citas disponibles",
        "paginate":{
            "next":">",
            "previous":"<"
        }
    }
});

// Función para filtrar citas por rango de fechas
$('#btn_filtrar').on('click', function() {
    const fechaInicio = $('#fecha_inicio').val();
    const fechaFin = $('#fecha_fin').val();
    
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Por favor seleccione ambas fechas'
        });
        return;
    }
    
    if (fechaInicio > fechaFin) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La fecha de inicio no puede ser mayor a la fecha fin'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        text: 'Filtrando citas',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Hacer petición AJAX
    $.ajax({
        url: '{{ url("/doctor/citas/filtrar") }}',
        method: 'GET',
        data: {
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        },
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Destruir la tabla actual
                table.destroy();
                
                // Actualizar el tbody
                let tbody = '';
                if (response.appointments.length > 0) {
                    response.appointments.forEach((appointment, index) => {
                        let statusBadge = '';
                        if (appointment.status == 0) {
                            statusBadge = '<span class="badge bg-warning text-dark">Pendiente</span>';
                        } else if (appointment.status == 1) {
                            statusBadge = '<span class="badge bg-success">Atendido</span>';
                        } else if (appointment.status == 2) {
                            statusBadge = '<span class="badge bg-danger">No Asistió</span>';
                        }
                        
                        tbody += `
                            <tr>
                                <td style="text-align: left">${index + 1}</td>
                                <td style="text-align: left">${appointment.patient.names} ${appointment.patient.surnames}</td>
                                <td style="text-align: left">${appointment.doctor.specialization.name}</td>
                                <td style="text-align: left">${appointment.date}</td>
                                <td style="text-align: left">${appointment.time}</td>
                                <td style="text-align: center">${statusBadge}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="/doctor/attend/edit/${appointment.id}" class="btn btn-primary btn-sm" style="background: #F4D03F !important;">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody = '<tr><td colspan="7" class="text-center">No hay citas en el rango seleccionado</td></tr>';
                }
                
                $('#tabla_citas_body').html(tbody);
                
                // Re-inicializar DataTables
                table = $('#especialidades').DataTable({
                    responsive: true,
                    autoWidth:false,
                    "language": {
                        "lengthMenu": "Mostrar "+
                                        `<select class="custom-select custom-select-sm w-50 form-select form-select-sm mb-2">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20">20</option>
                                        </select>`,
                        "zeroRecords": "No se encontraron citas",
                        "info": "Mostrando la página _PAGE_ de _PAGES_ de _TOTAL_ citas",
                        "infoEmpty": "No hay registros disponibles",
                        "infoFiltered": "(filtrado de _MAX_ registros totales)",
                        "search": "Buscar:",
                        "emptyTable": "No hay citas disponibles",
                        "paginate":{
                            "next":">",
                            "previous":"<"
                        }
                    }
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar las citas'
            });
        }
    });
});

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
        const text = number.textContent.trim();
        
        // Si contiene "/" (formato X/Y), no animar
        if (text.includes('/')) {
            return;
        }
        
        const target = parseInt(text);
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