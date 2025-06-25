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
                                            <option value="{{ $quota->id }}" {{ old('id_quota') == $quota->id ? 'selected' : '' }} {{ $quota->cupo_doctor == 0 ? 'disabled' : '' }}>
                                                {{ $quota->specialization->name }}
                                            </option>
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </div>
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
                                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                                            <td>{{ $appointment->patient->surnames }}, {{ $appointment->patient->names }}</td>
                                                                            <td class="text-center">{{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }}</td>
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
    console.log('🚀 Inicializando página de citas...');
    
    // Inicializar selectpickers y DataTables
    $('.selectpicker').selectpicker();

    var commonDataTableSettings = {
        responsive: true,
        autoWidth: false,
         pageLength: 10,
        language: {
            "lengthMenu": 'Mostrar <select class="form-select form-select-sm mb-1 me-1 ms-1"><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option></select> registros',
            "zeroRecords": "No se encontró nada",
            "info": "Página _PAGE_ de _PAGES_ (_TOTAL_ registros)",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "emptyTable": "Tabla sin datos",
            "paginate": { "next": ">", "previous": "<" }
        }
    };

    $('#asignaciones').DataTable($.extend({}, commonDataTableSettings, {
        "info": "Página _PAGE_ de _PAGES_ (_TOTAL_ doctores)"
    }));

    $('table.citas-today-table').each(function() {
        $(this).DataTable($.extend({}, commonDataTableSettings, {
            "info": "Página _PAGE_ de _PAGES_ (_TOTAL_ citas)",
            "lengthMenu": '<select class="form-select form-select-sm mb-1 me-1 ms-1"><option value="3">3</option><option value="5">5</option><option value="10">10</option></select>'
        }));
    });

    // Configurar horas disponibles
    var reservedHoursRaw = @json($hour->pluck('time')->all() ?? []);
    var reservedHours = reservedHoursRaw.map(function(time) {
        return time.slice(0, 5);
    });

    var selectTime = $('#time');
    var horasManana = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00'];
    var horasTarde = ['14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'];
    selectTime.empty();
    
    function agregarHorasOptgroup(horas, turnoLabel) {
        let optgroup = $(`<optgroup label="${turnoLabel}">`);
        let hasAvailableSlots = false;
        $.each(horas, function(key, value) {
            if (!reservedHours.includes(value)) {
                optgroup.append('<option value="' + value + '">' + value + '</option>');
                hasAvailableSlots = true;
            }
        });
        if (hasAvailableSlots) {
            selectTime.append(optgroup);
        }
    }
    
    agregarHorasOptgroup(horasManana, 'Turno Mañana');
    agregarHorasOptgroup(horasTarde, 'Turno Tarde');
    selectTime.selectpicker('refresh');

    // ⭐ MANEJO CORREGIDO DEL FORMULARIO
    // En la vista
$('#appointment-form').on('submit', function(event) {
    event.preventDefault(); // Mantenemos el preventDefault aquí por seguridad
    console.log('📝 Formulario enviado, delegando a Offline Manager...');
    
    const submitBtn = $('#submit-btn');
    const btnText = $('#btn-text');
    const btnSpinner = $('#btn-spinner');
    
    // Deshabilitar botón
    submitBtn.prop('disabled', true);
    btnText.addClass('d-none');
    btnSpinner.removeClass('d-none');

    // VERIFICACIÓN SEGURA
    if (window.recisaOffline && typeof window.recisaOffline.processForm === 'function') {
        
        // ✅ LLAMADA CORRECTA: Pasamos el objeto 'event' completo
        window.recisaOffline.processForm(event).finally(() => {
            // El `finally` aquí es opcional, ya que tu `processForm` no restaura el botón.
            // Es mejor dejar que `processForm` no maneje la UI para desacoplar.
            // La restauración del botón la podrías manejar en las alertas
            // o simplemente confiar en que el reset del form o la redirección lo soluciona.
            // Para ser seguros, lo restauramos aquí también.
            submitBtn.prop('disabled', false);
            btnText.removeClass('d-none');
            btnSpinner.addClass('d-none');
        });

    } else {
        // ... (Tu lógica de error si recisaOffline no existe) ...
        console.error('❌ recisaOffline no está disponible');
        submitBtn.prop('disabled', false);
        btnText.removeClass('d-none');
        btnSpinner.addClass('d-none');
        Swal.fire({
            icon: 'error',
            title: 'Error del Sistema',
            text: 'El sistema offline no está disponible. Por favor, recarga la página.'
        });
    }
});

    // Verificar estado inicial de conexión
    setTimeout(() => {
        if (!navigator.onLine) {
            console.log('📱 Página cargada en modo offline');
            if (window.recisaOffline && window.recisaOffline.showAlert) {
                window.recisaOffline.showAlert('offline', 'Modo Offline', 
                    'Trabajando sin conexión. Los datos se guardarán localmente.');
            }
        } else {
            console.log('🌐 Página cargada con conexión');
        }
    }, 2000);
    
    console.log('✅ Página de citas inicializada correctamente');
});
</script>
<!-- ✅ SCRIPT PARA ARREGLAR DATATABLES EN MODAL -->
<script>
$(document).ready(function() {
    // Inicializar DataTable cuando se muestre el modal
    $('#ver-{{ $doctor->id }}').on('shown.bs.modal', function () {
        // Buscar todas las tablas dentro de este modal
        $(this).find('.citas-today-table').each(function() {
            var $table = $(this);
            var tableId = $table.attr('id');
            
            // Si ya existe una instancia de DataTable, destruirla
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
            
            // Inicializar DataTable con configuración específica para modal
            $table.DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "paging": true,
                "pageLength": 10,
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activar para ordenar la columna ascendente",
                        "sortDescending": ": activar para ordenar la columna descendente"
                    }
                },
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "columnDefs": [
                    { "orderable": false, "targets": 0 }, // Deshabilitar ordenamiento en columna #
                    { "className": "text-center", "targets": [0, 2] } // Centrar columnas # y Hora
                ],
                "order": [[ 2, "asc" ]], // Ordenar por hora por defecto
                "drawCallback": function(settings) {
                    // Ajustar columnas después del dibujado
                    this.api().columns.adjust();
                },
                "initComplete": function(settings, json) {
                    // Ajustar columnas después de la inicialización completa
                    this.api().columns.adjust();
                }
            });
            
            // Ajustar columnas después de un breve delay
            setTimeout(function() {
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().columns.adjust();
                }
            }, 100);
        });
    });
    
    // Limpiar DataTable cuando se oculte el modal
    $('#ver-{{ $doctor->id }}').on('hidden.bs.modal', function () {
        $(this).find('.citas-today-table').each(function() {
            var tableId = $(this).attr('id');
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
        });
    });
    
    // ✅ SOLUCIÓN ADICIONAL: Manejar el redimensionamiento de ventana
    $(window).on('resize', function() {
        $('.citas-today-table').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().columns.adjust();
            }
        });
    });
});
</script>
@endpush