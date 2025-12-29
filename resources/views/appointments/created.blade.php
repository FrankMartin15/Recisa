{{-- resources/views/appointments/created.blade.php - VERSIÓN CORREGIDA --}}
@extends('layouts.app')
@section('title', 'Crear Cita')

@push('css')
<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.css') }}">
<!--Alertas (SweetAlert2)-->
<script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>

<style>
    /* Mejora de contraste SOLO para el select de Doctor/Especialidad */
    #id_quota + .bootstrap-select > .dropdown-toggle {
        background-color: #ffffff !important;
        border-color: #ced4da !important;
        color: #000000 !important;
    }

    #id_quota + .bootstrap-select > .dropdown-toggle .filter-option,
    #id_quota + .bootstrap-select > .dropdown-toggle .filter-option-inner-inner {
        color: #000000 !important;
    }

    /* En el dropdown, asegurar legibilidad de los encabezados (doctor) */
    #id_quota + .bootstrap-select .dropdown-menu .dropdown-header {
        color: #000000 !important;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="row mt-3">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Formulario de Registro de Citas</p>
                </div>

                <!-- COMPONENTE: Datos Pendientes de Sincronización -->
                <div id="pending-appointments-container" class="alert alert-info m-3 pending-sync-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Citas Pendientes de Sincronización:</strong>
                            <span id="pending-appointments-badge" class="badge bg-warning text-dark ms-2">0</span>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#pending-appointments-list">
                            Ver <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse mt-3" id="pending-appointments-list">
                        <div class="list-group" id="pending-appointments-items">
                            <!-- Lista dinámica -->
                        </div>
                    </div>
                </div>

                <style>
                    /* Animaciones suaves para el componente de datos pendientes */
                    #pending-appointments-container {
                        transition: opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease;
                        opacity: 1;
                        max-height: 500px;
                        overflow: hidden;
                    }

                    #pending-appointments-container.pending-sync-hidden {
                        opacity: 0;
                        max-height: 0;
                        margin: 0 !important;
                        padding: 0 !important;
                        pointer-events: none;
                    }

                    #pending-appointments-container.pending-sync-visible {
                        opacity: 1;
                        max-height: 500px;
                    }
                </style>

                <div class="card-body">
                    <div class="col-md-12">
                        @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle"></i>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('no_cupos'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sin Cupos Disponibles',
                                    text: '{{ session('no_cupos') }}',
                                    confirmButtonText: 'Entendido',
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                        @endif
                    </div>
                    
                    <form id="appointment-form" method="POST" action="{{ url('recisa/appoitnment/add') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <div class="input-group">
                                    <label class="input-group-text" for="id_quota">
                                        <i class="fa-solid fa-user-doctor text-primary"></i>
                                    </label>
                                    <select name="id_quota" id="id_quota" data-style="btn-secondary" data-live-search="true" data-size="3" class="form-control selectpicker" title="Seleccione el médico y especialidad" required {{ $isDoctorUser ? 'disabled' : '' }}>
                                        @foreach ($quotas as $quota)
                                        @php
                                            $citasHoy = $appointmentCounts[$quota->id] ?? 0;
                                            $cuposRestantes = $quota->cupo_doctor - $citasHoy;
                                        @endphp
                                        @if($isDoctorUser)
                                            {{-- Vista para doctores: información detallada --}}
                                            <optgroup label="{{ $quota->user->surnames }}, {{ $quota->user->names }} -> Cupos: {{ $quota->cupo_doctor }} | Citas hoy: {{ $citasHoy }} | Disponibles: {{ $cuposRestantes }}">
                                                <option value="{{ $quota->id }}" 
                                                        data-doctor-id="{{ $quota->user->id }}" 
                                                        data-cupos="{{ $quota->cupo_doctor }}"
                                                        data-citas="{{ $citasHoy }}"
                                                        data-specialization-id="{{ $quota->id_specialization }}"
                                                        data-specialization-total="{{ $quota->specialization->quantity_voucher }}"
                                                        data-tokens="{{ $quota->specialization->name }} {{ $quota->user->names }} {{ $quota->user->surnames }}"
                                                        {{ (old('id_quota') == $quota->id || ($isDoctorUser && $doctorQuotaId == $quota->id)) ? 'selected' : '' }} 
                                                        {{ $cuposRestantes <= 0 ? 'disabled' : '' }}>
                                                    {{ $quota->specialization->name }}
                                                </option>
                                            </optgroup>
                                        @else
                                            {{-- Vista para otros roles: información simple --}}
                                            <optgroup label="{{ $quota->user->surnames }}, {{ $quota->user->names }} -> cupos: {{ $quota->cupo_doctor }}">
                                                <option value="{{ $quota->id }}" 
                                                        data-doctor-id="{{ $quota->user->id }}"
                                                        data-tokens="{{ $quota->specialization->name }} {{ $quota->user->names }} {{ $quota->user->surnames }}" 
                                                        {{ old('id_quota') == $quota->id ? 'selected' : '' }} 
                                                        {{ $quota->cupo_doctor == 0 ? 'disabled' : '' }}>
                                                    {{ $quota->specialization->name }}
                                                </option>
                                            </optgroup>
                                        @endif
                                        @endforeach
                                    </select>
                                    @if($isDoctorUser)
                                        <input type="hidden" name="id_quota" value="{{ $doctorQuotaId }}">
                                        <button type="button" class="btn btn-outline-primary" id="btn-update-cupos" title="Actualizar Cupos">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    @endif
                                </div>
                                <small id="cupos-offline-notice" class="text-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> Modo offline: Los cupos y horas mostrados son aproximados
                                </small>
                            </div>

            <div class="col-md-12 mt-3">
                                <div class="input-group">
                                    <label class="input-group-text" for="id_patient">
                                        <i class="fa-solid fa-bed-pulse text-primary"></i>
                                    </label>
                                    <select name="id_patient" id="id_patient" title="Seleccione al Paciente..." data-style="btn-secondary" data-live-search="true" data-size="3" class="form-control selectpicker" required>
                                        @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('id_patient') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->surnames }}, {{ $patient->names }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Fecha -->
                            <div class="col-md-6 mt-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-calendar-days text-primary"></i>
                                    </span>
                                    <input type="date" name="date" id="date" class="form-control" 
                                           value="{{ old('date', \Carbon\Carbon::now('America/Lima')->format('Y-m-d')) }}" 
                                           min="{{ \Carbon\Carbon::now('America/Lima')->format('Y-m-d') }}" 
                                           required>
                                </div>
                            </div>
                            
                            <!-- Hora -->
                            <div class="col-md-6 mt-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-clock text-primary"></i>
                                    </span>
                                    <select name="time" id="time" data-style="btn-secondary" data-live-search="true" data-size="5" class="form-control selectpicker" title="Seleccione una hora" required>
                                        {{-- Opciones se llenan vía JavaScript --}}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12 text-center mt-2">
                                <button type="submit" id="submit-btn" class="btn btn-primary" style="background-color: #00476D !important;">
                                    <span id="btn-text">Guardar</span>
                                    <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Herramientas de Consulta</p>
                </div>
                <div class="card-body">
                    <div class="col-md-12 text-center">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" onclick="window.open('https://cel.sis.gob.pe/SisConsultaEnLinea', '_blank')" style="background-color: #00476D !important; border-color: #00476D;">
                                <i class="fa-solid fa-heart-pulse me-2"></i>
                                Consultar SIS en Línea
                                <i class="fa-solid fa-external-link-alt ms-2"></i>
                            </button>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Se abrirá en una nueva pestaña del navegador
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            {{-- Tabla de citas programadas para hoy --}}
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Citas Programadas Hoy</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive table mt-2">
                        <table class="table my-0" id="asignaciones">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Doctor</th>
                                    <th>Especialidades</th>
                                    <th>Ver Citas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($doctors as $index => $doctor)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $doctor->names }} {{ $doctor->surnames }}</td>
                                    <td>
                                        <ul class="list-group list-group-flush">
                                            @foreach ($doctor->specializations as $userSpecialization)
                                            <li class="list-group-item py-1">
                                                {{ $userSpecialization->specialization->name }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#ver-{{ $doctor->id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        
                                        <!-- Modal para ver citas -->
                                        <div class="modal fade" id="ver-{{ $doctor->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel-{{$doctor->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel-{{$doctor->id}}">
                                                            Citas para hoy (<strong>{{ $today }}</strong>) - Dr. {{ $doctor->names }} {{ $doctor->surnames }}
                                                        </h5>
                                                    </div>
                                                    <div class="modal-body">
                                                        @forelse ($doctor->specializations as $userSpecialization)
                                                            <h6 class="text-center mt-3" style="color: #00476D !important;">
                                                                {{ $userSpecialization->specialization->name }}
                                                            </h6>
                                                            @if($userSpecialization->appointment->where('date', $today)->where('status', 0)->count() > 0)
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-striped my-0 citas-today-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">#</th>
                                                                            <th>Paciente</th>
                                                                            <th class="text-center">Hora</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($userSpecialization->appointment->where('date', $today)->where('status', 0)->sortBy('time') as $appointment)
                                                                        <tr>
                                                                            <td class="text-center"></td> {{-- Dejar vacío, DataTables lo llenará --}}
                                                                            <td>{{ $appointment->patient->surnames }}, {{ $appointment->patient->names }}</td>
                                                                            <td class="text-center" data-order="{{ \Carbon\Carbon::parse($appointment->time)->format('H:i') }}">
                                                                                {{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }}
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @else
                                                            <p class="text-center text-muted">No hay citas programadas para esta especialidad hoy.</p>
                                                            @endif
                                                        @empty
                                                            <p class="text-center text-muted">El doctor no tiene especializaciones asignadas.</p>
                                                        @endforelse
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
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
<!-- Bootstrap Select JS -->
<script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
<!-- Offline Manager JS (versión corregida) -->
<script src="{{ asset('assets/js/offline-manager.v62.js') }}"></script>

<script>
$(document).ready(function() {
    // ⭐ CAMPO FECHA: SIEMPRE EDITABLE, PERO SOLO LUNES-VIERNES
    function isWeekendDateString(dateStr) {
        if (!dateStr) return false;
        const d = new Date(dateStr + 'T00:00:00');
        const day = d.getDay();
        return day === 0 || day === 6;
    }

    function nextWeekdayFrom(dateStrOrToday) {
        const base = dateStrOrToday
            ? new Date(dateStrOrToday + 'T00:00:00')
            : new Date(new Date().toISOString().split('T')[0] + 'T00:00:00');

        while (base.getDay() === 0 || base.getDay() === 6) {
            base.setDate(base.getDate() + 1);
        }

        return base.toISOString().split('T')[0];
    }

    function ensureWeekdaySelected(showAlert) {
        const dateField = $('#date');
        const current = dateField.val();

        // Si está vacío, setear a próximo día hábil
        if (!current) {
            dateField.val(nextWeekdayFrom(null));
            return;
        }

        if (isWeekendDateString(current)) {
            const fixed = nextWeekdayFrom(current);
            dateField.val(fixed);

            if (showAlert) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha no permitida',
                    text: 'Solo se permite seleccionar fechas de lunes a viernes. Se ajustó automáticamente al próximo día hábil.',
                });
            }
        }
    }

    function actualizarCampoFechaSegunConexion() {
        const isOnline = navigator.onLine;
        const dateField = $('#date');

        // Siempre editable; solo marcamos visualmente si está offline
        dateField.prop('readonly', false);
        if (!isOnline) {
            dateField.addClass('border-warning');
            dateField.attr('title', 'Modo offline: selección de fecha disponible (solo lunes a viernes)');
        } else {
            dateField.removeClass('border-warning');
            dateField.removeAttr('title');
        }
    }

    // ⭐ MOSTRAR/OCULTAR AVISO DE CUPOS APROXIMADOS EN MODO OFFLINE
    function actualizarAvisoCuposOffline() {
        const isOffline = !navigator.onLine;
        const notice = $('#cupos-offline-notice');

        if (isOffline) {
            notice.show();
        } else {
            notice.hide();
        }
    }

    // Ejecutar al cargar la página
    actualizarCampoFechaSegunConexion();
    actualizarAvisoCuposOffline();
    ensureWeekdaySelected(false);

    // Al cambiar la fecha, forzar lunes-viernes SIN afectar las horas
    $('#date').on('change', function() {
        ensureWeekdaySelected(true);
        // No modificar el select de horas al cambiar la fecha
    });

    // Listeners para cambios de conexión
    window.addEventListener('online', () => {
        actualizarCampoFechaSegunConexion();
        actualizarAvisoCuposOffline();
        ensureWeekdaySelected(false);
    });
    window.addEventListener('offline', () => {
        actualizarCampoFechaSegunConexion();
        actualizarAvisoCuposOffline();
        ensureWeekdaySelected(false);
    });

    // ⭐ GUARDAR DATOS ORIGINALES
    const originalQuotaOptions = $('#id_quota').html();
    const originalPatientOptions = $('#id_patient').html();
    
    // ⭐ FUNCIÓN PARA ACTUALIZAR EL MODAL DE CITAS
    function actualizarModalDeCitas(doctorId, patientName, appointmentTime) {
        console.log(`✍️ Actualizando modal para doctor ID: ${doctorId}`);
        
        const modalSelector = `#ver-${doctorId}`;
        const modal = $(modalSelector);
        
        if (modal.length === 0) {
            console.error(`Modal no encontrado para el doctor ID: ${doctorId}`);
            return;
        }
        
        // Formatear la hora a h:i A (ej. 03:30 PM)
        const timeParts = appointmentTime.split(':');
        const hours = parseInt(timeParts[0], 10);
        const minutes = timeParts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = ((hours + 11) % 12 + 1);
        const displayTime = `${formattedHours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
        
        // Buscar la tabla de citas dentro del modal
        const table = modal.find('.citas-today-table');
        
        if (table.length > 0) {
            const tbody = table.find('tbody');
            const newRowHtml = `
                <tr>
                    <td class="text-center">${tbody.find('tr').length + 1}</td>
                    <td>${patientName}</td>
                    <td class="text-center">${displayTime}</td>
                </tr>
            `;
            tbody.append(newRowHtml);
            console.log('✅ Fila de cita agregada a la tabla existente.');
        } else {
            // Si la tabla no existe (porque no había citas), la creamos
            console.log('⚠️ Tabla no encontrada, creando una nueva.');
            const specializationName = $('#id_quota option:selected').text().trim();
            const newTableHtml = `
                <h6 class="text-center mt-3" style="color: #00476D !important;">
                    ${specializationName}
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped my-0 citas-today-table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Paciente</th>
                                <th class="text-center">Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>${patientName}</td>
                                <td class="text-center">${displayTime}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
            
            // Reemplazar el mensaje de "no hay citas"
            const noAppointmentsMessage = modal.find('.modal-body').find('p:contains("No hay citas programadas")');
            if (noAppointmentsMessage.length > 0) {
                noAppointmentsMessage.first().replaceWith(newTableHtml);
            } else {
                modal.find('.modal-body').append(newTableHtml);
            }
            console.log('✅ Nueva tabla de citas creada y agregada al modal.');
        }
    }
    
    // ⭐ FUNCIÓN MEJORADA PARA RESTAURAR SELECTPICKERS
    function restaurarSelectPickersCompleto() {
        console.log('🔄 Restaurando selectpickers...');
        
        try {
            // Destruir selectpickers existentes
            $('#id_quota, #id_patient, #time').selectpicker('destroy');
        } catch(e) {
            // Ignorar errores de destrucción
        }
        
        // Restaurar HTML original SIN agregar opciones vacías
        $('#id_quota').html(originalQuotaOptions);
        $('#id_patient').html(originalPatientOptions);
        
        // Limpiar el select de tiempo completamente
        $('#time').empty();
        
        // Reinicializar selectpickers sin valores seleccionados
        $('#id_quota').selectpicker({
            title: 'Seleccione el médico y especialidad'
        });
        $('#id_patient').selectpicker({
            title: 'Seleccione al Paciente...'
        });
        $('#time').selectpicker({
            title: 'Seleccione una hora'
        });
        
        // Regenerar horas disponibles
        regenerarHorasDisponibles();
        
        console.log('✅ Selectpickers restaurados correctamente');
    }
    
    // ⭐ FUNCIÓN MEJORADA PARA REGENERAR HORAS
    // Horas reservadas (global dentro de este $(document).ready)
    let reservedHours = @json($hour->pluck('time')->all() ?? []).map(time => time.slice(0, 5));

    function regenerarHorasDisponibles() {
        console.log('🕐 Regenerando horas disponibles...');
        
        var selectTime = $('#time');
        // Preservar el valor seleccionado antes de regenerar
        const prevVal = selectTime.val();
        var horasManana = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00'];
        var horasTarde = ['14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
        
        console.log('⏰ Horas reservadas:', reservedHours);
        
        // Siempre mostrar ambos turnos; no filtrar por fecha/hora actual
        
        // Limpiar completamente el select
        selectTime.empty();
        
        function agregarHoras(horas, turno) {
            let optgroup = $(`<optgroup label="${turno}">`);
            let hasSlots = false;
            horas.forEach(hora => {
                // Filtrar horas reservadas
                if (reservedHours.includes(hora)) {
                    console.log('⏰ Hora descartada (reservada):', hora);
                    return;
                }
                
                console.log('✅ Hora agregada:', hora);
                optgroup.append(`<option value="${hora}">${hora}</option>`);
                hasSlots = true;
            });
            // Siempre agregar el optgroup para mostrar Turno Mañana/Tarde, aunque esté vacío
            selectTime.append(optgroup);
        }
        
        agregarHoras(horasManana, 'Turno Mañana');
        agregarHoras(horasTarde, 'Turno Tarde');
        
        // Si la hora previamente seleccionada sigue disponible, mantenerla
        if (prevVal && selectTime.find(`option[value="${prevVal}"]`).length > 0) {
            selectTime.val(prevVal);
        }
        // Refrescar el selectpicker y renderizar
        selectTime.selectpicker('refresh');
        selectTime.selectpicker('render');
        
        console.log('✅ Horas regeneradas, total opciones:', $('#time option').length);
        
        // Verificar que el selectpicker funcione correctamente
        setTimeout(() => {
            if ($('#time option').length === 0) {
                console.log('⚠️ No hay horas disponibles, regenerando...');
                // Si no hay opciones, agregar al menos una opción temporal
                selectTime.append('<option value="">No hay horas disponibles</option>');
                selectTime.selectpicker('refresh');
            }
        }, 100);
    }
    
    // Inicializar selectpickers
    $('.selectpicker').selectpicker();

    // Asegurar que al seleccionar una hora se refleje inmediatamente en el botón
    $('#time').on('changed.bs.select', function() {
        // Marcar que el formulario está en uso para evitar reseteos
        formHasData = true;
        resetEnabled = false;
        // Forzar refresco/render para que se muestre el texto seleccionado
        $('#time').selectpicker('refresh');
        $('#time').selectpicker('render');
        console.log('🕑 Hora seleccionada:', $('#time').val());
    });
    
    // FORZAR la fecha correcta (hoy) SIEMPRE al cargar la página
    var fechaHoyJS = new Date();
    // Usar fecha local del navegador (ya está en timezone correcto)
    var year = fechaHoyJS.getFullYear();
    var month = (fechaHoyJS.getMonth() + 1).toString().padStart(2, '0');
    var day = fechaHoyJS.getDate().toString().padStart(2, '0');
    var fechaHoyFormato = year + '-' + month + '-' + day;
    
    console.log('🔧 FORZANDO fecha al cargar...');
    console.log('🔧 Fecha local del navegador:', fechaHoyJS.toString());
    console.log('🔧 Fecha hoy formateada:', fechaHoyFormato);
    
    // SIEMPRE establecer la fecha de hoy si no hay old('date')
    var oldDate = '{{ old("date") }}';
    if (!oldDate || oldDate === '') {
        console.log('✅ Estableciendo fecha de hoy en el input');
        $('#date').val(fechaHoyFormato);
    } else {
        console.log('⚠️ Hay old date:', oldDate);
        // Verificar si old date es anterior a hoy, si es así, usar hoy
        if (oldDate < fechaHoyFormato) {
            console.log('⚠️ Old date es anterior a hoy, usando hoy');
            $('#date').val(fechaHoyFormato);
        }
    }
    
    console.log('🔧 Fecha final en input:', $('#date').val());

    // Configurar horas disponibles INICIALMENTE (después de asegurar la fecha)
    setTimeout(function() {
        regenerarHorasDisponibles();
    }, 100);

    // ⭐ CONFIGURAR DATATABLES UNA SOLA VEZ
    if (!$.fn.DataTable.isDataTable('#asignaciones')) {
        $('#asignaciones').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                "lengthMenu": 'Mostrar <select class="form-select form-select-sm mb-1 me-1 ms-1"><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option></select> registros',
                "zeroRecords": "No se encontró nada",
                "info": "Página _PAGE_ de _PAGES_ (_TOTAL_ doctores)",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "emptyTable": "Tabla sin datos",
                "paginate": { "next": ">", "previous": "<" }
            }
        });
    }

    // ⭐ MANEJAR DATATABLES EN MODALES (CORREGIDO Y FINAL)
    $('[id^="ver-"]').on('shown.bs.modal', function() {
        var modal = $(this);
        modal.find('.citas-today-table').each(function() {
            var tableEl = $(this);
            if (!$.fn.DataTable.isDataTable(tableEl)) {
                var dt = tableEl.DataTable({
                    responsive: true,
                    pageLength: 5,
                    searching: false,
                    lengthChange: true, // Activado para mostrar el menú
                    info: true,
                    paging: true,
                    order: [[2, "asc"]], // Ordenar por la columna de hora (índice 2)
                    language: { // Traducciones completas
                        "lengthMenu": 'Mostrar <select class="form-select form-select-sm mb-1 me-1 ms-1"><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option></select> registros',
                        "info": "Página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay citas",
                        "infoFiltered": "",
                        "paginate": { "next": ">", "previous": "<" },
                        "zeroRecords": "No hay citas para mostrar"
                    },
                    "columnDefs": [{
                        "searchable": false,
                        "orderable": false,
                        "targets": 0
                    }]
                });

                // Función para la numeración automática
                dt.on('order.dt search.dt', function () {
                    dt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                        cell.innerHTML = i + 1;
                    } );
                }).draw();
            }
        });
    });

    // Limpiar DataTables al cerrar modales
    $('[id^="ver-"]').on('hidden.bs.modal', function() {
        $(this).find('.citas-today-table').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }
        });
    });

    // ⭐ MANEJO DEL FORMULARIO (VERSIÓN CORREGIDA)
    $('#appointment-form').on('submit', function(event) {
        event.preventDefault();

        // Marcar que el formulario está en proceso de envío
        formHasData = true;
        resetEnabled = false;
        console.log('📝 Formulario en proceso de envío - Reseteo bloqueado');

        // Asegurar que la fecha enviada sea día hábil
        ensureWeekdaySelected(true);
        
        const submitBtn = $('#submit-btn');
        const btnText = $('#btn-text');
        const btnSpinner = $('#btn-spinner');
        const form = $(this);
        
        // Deshabilitar botón
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        btnSpinner.removeClass('d-none');
        
        // Verificar si estamos online
        const isOnline = navigator.onLine;
        
        if (isOnline) {
            // ========== MODO ONLINE: Enviar al servidor con AJAX ==========
            console.log('📡 Modo ONLINE: Enviando al servidor');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: { 'Accept': 'application/json' },
                success: function(response) {
                    console.log('✅ Respuesta del servidor:', response);
                    
                    // Limpiar formulario
                    form[0].reset();
                    $('#date').val(new Date().toISOString().split('T')[0]);
                    restaurarSelectPickersCompleto();
                    regenerarHorasDisponibles();
                    
                    // Reactivar reseteo automático después de guardar exitosamente
                    resetEnabled = true;
                    formHasData = false;
                    console.log('♻️ Reseteo automático reactivado después de guardar cita');
                    
                    // Restaurar botón
                    submitBtn.prop('disabled', false);
                    btnText.removeClass('d-none');
                    btnSpinner.addClass('d-none');
                    
                    // Mostrar mensaje de éxito (toast arriba-derecha)
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Cita registrada'
                    });

                    // Recargar para actualizar la tabla/modales de "Citas Programadas Hoy"
                    setTimeout(() => location.reload(), 1200);
                },
                error: function(xhr) {
                    console.error('❌ Error del servidor:', xhr);
                    
                    // Mantener el formulario sin resetear para que el usuario pueda corregir
                    // NO reactivamos el reseteo aquí para que el usuario conserve sus datos
                    console.log('⚠️ Error al guardar - Formulario conservado para corrección');
                    
                    // Restaurar botón
                    submitBtn.prop('disabled', false);
                    btnText.removeClass('d-none');
                    btnSpinner.addClass('d-none');
                    
                    let errorMessage = 'Error al registrar la cita.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(' • ');
                    }

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    Toast.fire({
                        icon: 'error',
                        title: errorMessage
                    });
                }
            });
            
        } else {
            // ========== MODO OFFLINE: Usar sistema offline ==========
            console.log('📴 Modo OFFLINE: Guardando localmente');
            
            if (window.recisaOffline && typeof window.recisaOffline.processForm === 'function') {
                // Obtener datos antes de procesar
                const selectedQuotaOption = $('#id_quota option:selected');
                const doctorId = selectedQuotaOption.data('doctor-id');
                const patientName = $('#id_patient option:selected').text().trim();
                const appointmentTime = $('#time').val();
                
                // Procesar formulario offline
                window.recisaOffline.processForm(event);
                
                // Restauración después del envío
                setTimeout(() => {
                    form[0].reset();
                    $('#date').val(new Date().toISOString().split('T')[0]);
                    restaurarSelectPickersCompleto();
                    regenerarHorasDisponibles();
                    
                    // Reactivar reseteo automático después de guardar exitosamente en modo offline
                    resetEnabled = true;
                    formHasData = false;
                    console.log('♻️ Reseteo automático reactivado después de guardar cita (modo offline)');
                    
                    // Actualizar modal si es necesario
                    if (doctorId && patientName && appointmentTime) {
                        actualizarModalDeCitas(doctorId, patientName, appointmentTime);
                    }
                    
                    // Restaurar botón
                    submitBtn.prop('disabled', false);
                    btnText.removeClass('d-none');
                    btnSpinner.addClass('d-none');
                    
                    // Mostrar mensaje de éxito offline
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Cita guardada localmente'
                    });
                }, 500);
                
            } else {
                // Si no hay sistema offline disponible
                // Mantener el formulario sin resetear para que el usuario pueda intentar de nuevo
                console.log('⚠️ Sistema offline no disponible - Formulario conservado');
                
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                btnSpinner.addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Sistema offline no disponible. Necesita conexión a internet.'
                });
            }
        }
    });

    // ⭐ VARIABLES PARA CONTROLAR INTERACCIÓN DEL USUARIO Y RESETEO
    let userIsInteracting = false;
    let lastInteractionTime = Date.now();
    let resetEnabled = true; // Controla si el reseteo automático está habilitado
    let formHasData = false; // Bandera para detectar si el formulario tiene datos

    // Detectar cuando el usuario está seleccionando opciones
    $('#id_quota, #id_patient, #time, #date').on('focus mousedown', function() {
        userIsInteracting = true;
        lastInteractionTime = Date.now();
    });

    $('#id_quota, #id_patient, #time, #date').on('blur change', function() {
        setTimeout(() => {
            userIsInteracting = false;
        }, 2000); // Esperar 2 segundos después de la última interacción
    });

    // Función para verificar si el formulario tiene datos
    function checkFormHasData() {
        const quotaValue = $('#id_quota').val();
        const patientValue = $('#id_patient').val();
        const timeValue = $('#time').val();
        const dateValue = $('#date').val();
        
        // Si cualquier campo tiene un valor válido, el formulario tiene datos
        const hasData = (quotaValue && quotaValue.trim() !== '') || 
                       (patientValue && patientValue.trim() !== '') || 
                       (timeValue && timeValue.trim() !== '');
        
        if (hasData) {
            formHasData = true;
            resetEnabled = false; // Detener el reseteo automático
            console.log('✅ Formulario en uso - Reseteo deshabilitado');
        } else {
            formHasData = false;
            resetEnabled = true; // Reactivar el reseteo automático
            console.log('⚪ Formulario vacío - Reseteo habilitado');
        }
        
        return hasData;
    }

    // Detectar cuando se llena cualquier campo del formulario
    $('#id_quota, #id_patient, #time, #date').on('change', function() {
        checkFormHasData();
    });

    // ⭐ VERIFICACIÓN INTELIGENTE DE DUPLICADOS (NO INTERFIERE CON INTERACCIÓN)
    setInterval(function() {
        // Evitar resetear mientras el usuario interactúa con los selects
        if ($('.bootstrap-select.show').length > 0) {
            console.log('⏸️ Reseteo pausado - Dropdown abierto');
            return;
        }

        // No resetear si el formulario tiene datos y el reseteo está deshabilitado
        if (!resetEnabled || formHasData) {
            console.log('⏸️ Reseteo pausado - Formulario tiene datos o está en uso');
            return;
        }

        function hasEmptyOrDuplicateOptions($options) {
            const seen = new Set();
            for (const opt of $options) {
                const value = (opt.value ?? '').trim();
                if (value === '') {
                    return true;
                }
                if (seen.has(value)) {
                    return true;
                }
                seen.add(value);
            }
            return false;
        }

        const quotaOptions = $('#id_quota option');
        const patientOptions = $('#id_patient option');

        // Solo restaurar si realmente hay opciones vacías/duplicadas
        if (hasEmptyOrDuplicateOptions(quotaOptions) || hasEmptyOrDuplicateOptions(patientOptions)) {
            console.log('⚠️ Opciones duplicadas/vacías detectadas, restaurando...');
            restaurarSelectPickersCompleto();
        }
    }, 60000); // Cambiado de 15000 (15 segundos) a 60000 (60 segundos)

    // ⭐ FUNCIÓN PARA MOSTRAR CITAS PENDIENTES DE SINCRONIZACIÓN
    async function loadPendingAppointments() {
        const container = document.getElementById('pending-appointments-container');
        const badge = document.getElementById('pending-appointments-badge');
        const list = document.getElementById('pending-appointments-items');

        try {
            // Abrir IndexedDB
            const dbRequest = indexedDB.open('recisa-offline-db', 51);

            dbRequest.onsuccess = (event) => {
                const db = event.target.result;
                const transaction = db.transaction(['pending-requests'], 'readonly');
                const store = transaction.objectStore('pending-requests');
                const getAllRequest = store.getAll();

                getAllRequest.onsuccess = () => {
                    const allPending = getAllRequest.result;

                    // Filtrar solo las citas (por URL)
                    const pendingAppointments = allPending.filter(req =>
                        req.url && (req.url.includes('/appointments/add') || req.url.includes('/appoitnment/add') || req.url.includes('/appointments/insert'))
                    );

                    if (pendingAppointments.length > 0) {
                        // Mostrar container con animación suave
                        container.classList.remove('pending-sync-hidden');
                        container.classList.add('pending-sync-visible');
                        badge.textContent = pendingAppointments.length;

                        // Limpiar lista
                        list.innerHTML = '';

                        // Agregar cada cita a la lista
                        pendingAppointments.forEach((req, index) => {
                            const data = req.body || {};

                            // Buscar nombres de paciente y doctor en los selectores
                            const patientName = $(`#id_patient option[value="${data.id_patient}"]`).text() || 'Paciente desconocido';
                            const quotaText = $(`#id_quota option[value="${data.id_quota}"]`).text() || 'Especialidad desconocida';

                            const item = document.createElement('div');
                            item.className = 'list-group-item list-group-item-action';
                            item.innerHTML = `
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas fa-calendar-check"></i> ${patientName}</h6>
                                    <small class="text-muted">${new Date(req.timestamp).toLocaleString()}</small>
                                </div>
                                <p class="mb-1 small">
                                    <strong>Especialidad:</strong> ${quotaText}<br>
                                    <strong>Fecha:</strong> ${data.date || 'N/A'} |
                                    <strong>Hora:</strong> ${data.time || 'N/A'}
                                </p>
                            `;
                            list.appendChild(item);
                        });
                    } else {
                        // Ocultar container con animación suave
                        container.classList.remove('pending-sync-visible');
                        container.classList.add('pending-sync-hidden');
                    }
                };
            };

            dbRequest.onerror = () => {
                console.error('Error al abrir IndexedDB');
            };
        } catch (error) {
            console.error('Error al cargar citas pendientes:', error);
        }
    }

    // Cargar citas pendientes al iniciar
    loadPendingAppointments();

    // Actualizar cada 10 segundos
    setInterval(loadPendingAppointments, 10000);

    // Actualizar cuando cambie el estado de conexión
    window.addEventListener('online', loadPendingAppointments);
    window.addEventListener('offline', loadPendingAppointments);

    // Botón para actualizar cupos (solo para doctores)
    @if($isDoctorUser)
    $('#btn-update-cupos').on('click', function() {
        const selectedOption = $('#id_quota option:selected');
        const cuposActuales = parseInt(selectedOption.data('cupos')) || 0;
        const citasHoy = parseInt(selectedOption.data('citas')) || 0;
        const specializationTotal = parseInt(selectedOption.data('specialization-total')) || 0;
        const quotaId = selectedOption.val();
        
        Swal.fire({
            title: 'Actualizar Cupos',
            html: `
                <div class="text-start">
                    <p><strong>Información actual:</strong></p>
                    <ul>
                        <li>Cupos asignados: <strong>${cuposActuales}</strong></li>
                        <li>Citas registradas hoy: <strong>${citasHoy}</strong></li>
                        <li>Total de cupos de la especialidad: <strong>${specializationTotal}</strong></li>
                    </ul>
                    <div class="mb-3">
                        <label for="nuevos-cupos" class="form-label">Nuevos cupos a asignar:</label>
                        <input type="number" id="nuevos-cupos" class="form-control" 
                               min="${citasHoy}" 
                               max="${specializationTotal}" 
                               value="${cuposActuales}"
                               placeholder="Ingrese nuevos cupos">
                        <small class="text-muted">Mínimo: ${citasHoy} (citas ya registradas) | Máximo: ${specializationTotal} (total de la especialidad)</small>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const nuevosCupos = parseInt(document.getElementById('nuevos-cupos').value);
                
                if (isNaN(nuevosCupos)) {
                    Swal.showValidationMessage('Debe ingresar un número válido');
                    return false;
                }
                
                if (nuevosCupos < citasHoy) {
                    Swal.showValidationMessage(`No puede asignar menos de ${citasHoy} cupos porque ya tiene ${citasHoy} citas registradas hoy`);
                    return false;
                }
                
                if (nuevosCupos > specializationTotal) {
                    Swal.showValidationMessage(`No puede asignar más de ${specializationTotal} cupos (total de la especialidad)`);
                    return false;
                }
                
                return nuevosCupos;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar actualización al servidor
                $.ajax({
                    url: '{{ url("recisa/quota/update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quota_id: quotaId,
                        nuevo_cupo: result.value
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cupos actualizados',
                            text: `Se han actualizado los cupos a ${result.value}`,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al actualizar los cupos';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
    @endif

});
</script>
@endpush