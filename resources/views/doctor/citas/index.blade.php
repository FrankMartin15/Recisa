@extends('layouts.app')
@section('title','Citas')
    @push('css')
        <!--Alertas-->
        <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
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
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 fw-bold">Citas Pendientes</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table" id="dataTable-2" role="grid" aria-describedby="dataTable_info">
                            <table id="especialidades" class="table my-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20px; font-weight:bold; text-align:center">#</th>
                                        <th style="width: 450px; font-weight:bold; text-align:center">Paciente</th>
                                        <th style="width: 200px; font-weight:bold; text-align:center">Especialidad</th>
                                        <th style="width: 200px; font-weight:bold; text-align:center">Fecha</th>
                                        <th style="width: 100px; font-weight:bold; text-align:center">Hora</th>
                                        <th style="font-weight:bold; text-align:center" class="text-center">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointments as $value => $appointment)
                                        <tr>
                                            <td style="text-align: left">{{ $value + 1 }}</td>
                                            <td style="text-align: left">{{ $appointment->patient->names }} {{ $appointment->patient->surnames }}</td>
                                            <td style="text-align: left">{{ $appointment->doctor->specialization->name }}</td>
                                            <td style="text-align: left">{{ $appointment->date }}</td>
                                            <td style="text-align: left">{{ $appointment->time }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ url('doctor/attend/edit/'.$appointment->id) }}" class="btn btn-primary" style="background: #F4D03F !important;">
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
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="text-primary fw-bold m-0">Avance de Atención</h6>
                    </div>
                    <div class="card-body">
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
                            <h4 class="small fw-bold">{{$atendido->specialization->name}}
                                @if ($maxquatity == 100)
                                    <span class="float-end">Completado !</span>
                                @else
                                    <span class="float-end"><?php echo round($maxquatity); ?>%</span>
                                @endif
                            </h4>
                            <div class="progress progress-sm mb-3">
                                <div class="progress-bar bg-success" aria-valuenow="<?php echo $maxquatity; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $maxquatity; ?>%;">
                                    <span class="visually"><?php echo round($maxquatity); ?>%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>            
        </div>   
    @endsection
    @push('js')
        <script>
            $('#especialidades').DataTable({
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
                    "info": "Mostrando la página _PAGE_ de _PAGES_ de _TOTAL_ citas pendientes",
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
        </script>    
    @endpush