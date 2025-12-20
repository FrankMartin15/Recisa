@extends('layouts.app')
@section('title','Citas')
    @push('css')
        <!--Alertas-->
        <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
        <!--CSS TABLA-->
        <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.css') }}">      
    @endpush 
    @section('content')
        @if (session('success'))
            <script>
                let message ="{{session('success')}}";
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: message
                });
            </script>            
        @endif
        @if (session('error'))
            <script>
                let errorMessage ="{{session('error')}}";
                const ToastError = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                ToastError.fire({
                    icon: "error",
                    title: errorMessage
                });
            </script>            
        @endif   

        <div class="d-sm-flex align-items-center mb-4" style="justify-content: right;">
            <a class="btn btn-primary btn-sm d-none d-sm-inline-block offline-hide" role="button" data-bs-toggle="modal"
                data-bs-target="#appointmentReportModal"
                style="--bs-primary: #00486E;--bs-primary-rgb: 0,72,110;--bs-body-bg: #00476D;background: #00476D !important;">
                <i class="fas fa-download fa-sm text-white-50"></i>&nbsp;Generar Reporte
            </a>
        </div>

        {{-- Modal --}}
        <div class="modal fade" id="appointmentReportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="appointmentReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form method="GET" action="{{ url('/recisa/appointments/reporte') }}" target="_blank">
                        @csrf
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="appointmentReportModalLabel">Filtros para Reporte de Citas</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="input-group">
                                        <label class="input-group-text">Fecha Inicio</label>
                                        <input type="date" class="form-control" name="start_date" required value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="input-group">
                                        <label class="input-group-text">Fecha Fin</label>
                                        <input type="date" class="form-control" name="end_date" required value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-2">
                                    <div class="input-group">
                                        <label class="input-group-text">Especialidad</label>
                                        <select name="id_specialization" data-style="btn-secondary" data-live-search="true"
                                            data-size="5" class="form-control selectpicker">
                                            <option value="" selected>Todas</option>
                                            @foreach(($filterSpecializations ?? collect()) as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-2">
                                    <div class="input-group">
                                        <label class="input-group-text">Doctor</label>
                                        <select name="id_doctor" data-style="btn-secondary" data-live-search="true"
                                            data-size="5" class="form-control selectpicker">
                                            <option value="" selected>Todos</option>
                                            @foreach(($filterDoctors ?? collect()) as $doc)
                                                <option value="{{ $doc->id }}">{{ ($doc->surnames ?? '') . ', ' . ($doc->names ?? '') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-2">
                                    <div class="input-group">
                                        <label class="input-group-text">Paciente</label>
                                        <select name="id_patient" data-style="btn-secondary" data-live-search="true"
                                            data-size="5" class="form-control selectpicker">
                                            <option value="" selected>Todos</option>
                                            @foreach(($filterPatients ?? collect()) as $pat)
                                                <option value="{{ $pat->id }}">{{ ($pat->surnames ?? '') . ', ' . ($pat->names ?? '') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small class="text-muted">Si seleccionas paciente, el reporte incluye todas sus especialidades en el rango.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger" style="background: #EB5C5E;">Realizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Lista de Citas</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table" id="dataTable-2" role="grid" aria-describedby="dataTable_info">
                            <table id="citas" class="table my-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20px; font-weight:bold; text-align:center">#</th>
                                        <th style="width: 400px;font-weight:bold;">Paciente</th>
                                        <th style="width: 400px;font-weight:bold;">Doctor</th>
                                        <th style="width: 250px;font-weight:bold;">Especialidad</th>
                                        <th style="width: 200px;font-weight:bold;">Fecha</th>
                                        <th style="width: 150px;font-weight:bold;">Hora</th>
                                        <th style="text-align: center; width: 200px;font-weight:bold;">Estado</th>
                                        <th style="text-align: center;font-weight:bold;">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointments as $index=>$appointment)
                                        <tr>
                                            <td>{{$index + 1}}</td>
                                            <td>{{$appointment->patient->names}} {{$appointment->patient->surnames}}</td>
                                            <td>{{$appointment->doctor->user->names}} {{$appointment->doctor->user->surnames}}</td>
                                            <td>{{$appointment->doctor->specialization->name}}</td>
                                            <td>{{date('d-m-Y', strtotime($appointment->date))}}</td>
                                            <td>{{$appointment->time}}</td>
                                            <td class="text-center">
                                                @switch($appointment->status)
                                                    @case(0)
                                                        <span class="fw-bolder p-1 rounded border border-warning border-2">Por atender</span>
                                                        @break
                                                    @case(1)
                                                        <span class="fw-bolder p-1 rounded border border-success border-2">Atendido</span>
                                                        @break
                                                    @case(2)
                                                        <span class="fw-bolder p-1 rounded border border-danger border-2">No Asistio</span>
                                                        @break
                                                    @default
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{url('recisa/appoitnment/show/'.$appointment->id)}}" class="btn btn-primary" style="background: #48C9B0 !important;"><i class="fa-solid fa-eye"></i></a>
                                                    @if($appointment->status == 0)
                                                        <button type="button" class="btn btn-danger" style="background: #EB5C5E;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal-{{ $appointment->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                <!-- Modal de Eliminación -->
                                                @if($appointment->status == 0)
                                                <div class="modal fade" id="deleteModal-{{ $appointment->id }}"
                                                    data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                    aria-labelledby="deleteModalLabel-{{ $appointment->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="deleteModalLabel-{{ $appointment->id }}">
                                                                    Desea Eliminar la Cita</h1>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Se eliminará la cita del paciente <strong>{{$appointment->patient->names}} {{$appointment->patient->surnames}}</strong> 
                                                                programada para el <strong>{{date('d-m-Y', strtotime($appointment->date))}}</strong> a las <strong>{{$appointment->time}}</strong>.</p>
                                                                <p class="text-muted small">El cupo será liberado automáticamente.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                                <a href="{{ url('recisa/appointments/delete/' . $appointment->id) }}"
                                                                    class="btn btn-danger" style="background: #EB5C5E;">
                                                                    Eliminar
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
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
    @endsection
    @push('js')
        <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
        <script>
            $('#citas').DataTable({
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
                    "zeroRecords": "No se encontró nada - lo siento",
                    "info": "Mostrando la página _PAGE_ de _PAGES_ de _TOTAL_ citas",
                    "infoEmpty": "No hay registros disponibles",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "paginate":{
                        "next":">",
                        "previous":"<"
                    }
                }
            });

            // Inicializar selectpickers del modal de reporte
            $('.selectpicker').selectpicker();
        </script>    
    @endpush