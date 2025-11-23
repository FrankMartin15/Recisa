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
                <div id="pending-appointments-container" class="alert alert-info m-3" style="display: none;">
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
                    </div>
                    
                    <form id="appointment-form" method="POST" action="{{ url('recisa/appointments/add') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <div class="input-group">
                                    <label class="input-group-text" for="id_quota">
                                        <i class="fa-solid fa-user-doctor text-primary"></i>
                                    </label>
                                    <select name="id_quota" id="id_quota" data-style="btn-secondary" data-live-search="true" data-size="3" class="form-control selectpicker" title="Seleccione el médico y especialidad" required>
                                        @foreach ($quotas as $quota)
                                        <optgroup label="{{ $quota->user->surnames }}, {{ $quota->user->names }} -> cupos: {{ $quota->cupo_doctor }}">
                                            <option value="{{ $quota->id }}" data-doctor-id="{{ $quota->user->id }}" {{ old('id_quota') == $quota->id ? 'selected' : '' }} {{ $quota->cupo_doctor == 0 ? 'disabled' : '' }}>
                                                {{ $quota->specialization->name }}
                                            </option>
                                        </optgroup>
                                        @endforeach
                                    </select>
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
                                    <input readonly type="date" name="date" id="date" class="form-control" value="{{ date('Y-m-d') }}" required>
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
<script src="{{ asset('assets/js/offline-manager.js?v=' . time()) }}"></script>

<script>
$(document).ready(function() {
    // ⭐ HABILITAR/DESHABILITAR CAMPO FECHA SEGÚN MODO OFFLINE
    function actualizarCampoFechaSegunConexion() {
        const isOnline = navigator.onLine;
        const dateField = $('#date');

        if (!isOnline) {
            // Modo offline: habilitar campo fecha
            dateField.prop('readonly', false);
            dateField.addClass('border-warning');
            dateField.attr('title', 'Campo editable en modo offline');
        } else {
            // Modo online: mantener readonly
            dateField.prop('readonly', true);
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

    // Listeners para cambios de conexión
    window.addEventListener('online', () => {
        actualizarCampoFechaSegunConexion();
        actualizarAvisoCuposOffline();
    });
    window.addEventListener('offline', () => {
        actualizarCampoFechaSegunConexion();
        actualizarAvisoCuposOffline();
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
    function regenerarHorasDisponibles() {
        console.log('🕐 Regenerando horas disponibles...');
        
        var selectTime = $('#time');
        var horasManana = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00'];
        var horasTarde = ['14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'];
        
        var reservedHours = @json($hour->pluck('time')->all() ?? []).map(time => time.slice(0, 5));
        console.log('⏰ Horas reservadas:', reservedHours);
        
        // Limpiar completamente el select
        selectTime.empty();
        
        function agregarHoras(horas, turno) {
            let optgroup = $(`<optgroup label="${turno}">`);
            let hasSlots = false;
            horas.forEach(hora => {
                if (!reservedHours.includes(hora)) {
                    optgroup.append(`<option value="${hora}">${hora}</option>`);
                    hasSlots = true;
                }
            });
            if (hasSlots) {
                selectTime.append(optgroup);
            }
        }
        
        agregarHoras(horasManana, 'Turno Mañana');
        agregarHoras(horasTarde, 'Turno Tarde');
        
        // Refrescar el selectpicker
        selectTime.selectpicker('refresh');
        
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

    // Configurar horas disponibles INICIALMENTE
    regenerarHorasDisponibles();

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

    // ⭐ MANEJO OPTIMIZADO DEL FORMULARIO
    $('#appointment-form').on('submit', function(event) {
        event.preventDefault();
        
        const submitBtn = $('#submit-btn');
        const btnText = $('#btn-text');
        const btnSpinner = $('#btn-spinner');
        
        // Deshabilitar botón
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        btnSpinner.removeClass('d-none');

        if (window.recisaOffline && typeof window.recisaOffline.processForm === 'function') {
            // Obtener datos ANTES de procesar (para marcar hora como reservada)
            const selectedQuotaOption = $('#id_quota option:selected');
            const doctorId = selectedQuotaOption.data('doctor-id');
            const patientName = $('#id_patient option:selected').text().trim();
            const appointmentTime = $('#time').val();
            const appointmentDate = $('#date').val();
            const isOffline = !navigator.onLine;

            // Procesar formulario
            window.recisaOffline.processForm(event);

            // Si guardó offline, marcar hora como reservada para evitar duplicados
            if (isOffline && appointmentTime) {
                // Agregar la hora al array de horas reservadas
                if (!reservedHours.includes(appointmentTime)) {
                    reservedHours.push(appointmentTime);
                    console.log('⏰ Hora marcada como reservada (offline):', appointmentTime);
                }
            }

            // Restauración después del envío
            setTimeout(() => {
                $('#appointment-form')[0].reset();
                $('#date').val(new Date().toISOString().split('T')[0]);
                restaurarSelectPickersCompleto();

                // Regenerar horas disponibles (ahora sin la hora recién reservada)
                regenerarHorasDisponibles();

                // Actualizar el modal con la nueva cita
                if (doctorId && patientName && appointmentTime) {
                    actualizarModalDeCitas(doctorId, patientName, appointmentTime);
                }

                // Verificación adicional para evitar opciones en blanco
                setTimeout(() => {
                    verificarDuplicados();
                }, 500);

                // Mostrar mensaje de éxito (diferente si es offline)
                const message = isOffline
                    ? 'Cita guardada localmente. Se enviará cuando vuelva la conexión.'
                    : 'La cita ha sido registrada exitosamente y se mandó un mensaje al paciente.';

                Swal.fire({
                    icon: 'success',
                    title: 'Cita Registrada',
                    text: message,
                    timer: isOffline ? 3000 : 2000,
                    showConfirmButton: false
                });

                // Restaurar botón
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                btnSpinner.addClass('d-none');
            }, 1000);

        } else {
            submitBtn.prop('disabled', false);
            btnText.removeClass('d-none');
            btnSpinner.addClass('d-none');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Sistema offline no disponible. Recarga la página.'
            });
        }
    });

    // ⭐ VERIFICACIÓN SIMPLE DE DUPLICADOS CADA 15 SEGUNDOS
    setInterval(function() {
        console.log('� Verificación periódica de duplicados...');

        const quotaOptions = $('#id_quota option');
        const patientOptions = $('#id_patient option');

        // Si hay demasiadas opciones (más del doble esperado), restaurar
        if (quotaOptions.length > 20 || patientOptions.length > 40) {
            console.log('⚠️ Demasiadas opciones detectadas, restaurando...');
            restaurarSelectPickersCompleto();
        }
    }, 15000);

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
                        // Mostrar container
                        container.style.display = 'block';
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
                        container.style.display = 'none';
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

});
</script>
@endpush