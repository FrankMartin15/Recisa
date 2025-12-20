@extends('layouts.app')
@section('title', 'Roles')
@push('css')
    <!--Alertas-->
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <style>
        /* Estilos para el tooltip personalizado */
        .user-tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
            text-decoration: underline dotted;
        }
        .user-tooltip .tooltip-content {
            visibility: hidden;
            opacity: 0;
            background-color: #333;
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 10px;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            min-width: 200px;
            max-width: 300px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: opacity 0.3s, visibility 0.3s;
        }
        .user-tooltip .tooltip-content::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }
        .user-tooltip:hover .tooltip-content {
            visibility: visible;
            opacity: 1;
        }
        .tooltip-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .tooltip-content li {
            padding: 3px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 12px;
        }
        .tooltip-content li:last-child {
            border-bottom: none;
        }
    </style>
@endpush
@section('content')
    @if (session('success'))
        <script>
            let message = "{{ session('success') }}";
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
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
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Lista de Roles</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive table" id="dataTable-2" role="grid" aria-describedby="dataTable_info">
                        <table class="table my-0" id="dataTable">
                            <thead>
                                <tr>
                                    <th style="width: 20px; font-weight:bold; text-align:center">#</th>
                                    <th style="width: 250px; font-weight:bold; text-align:center">Nivel de Usuario</th>
                                    <th style="width: 200px; font-weight:bold; text-align:center">Estado</th>
                                    <th style="width: 200px; font-weight:bold; text-align:center">Número de usuarios</th>
                                    <th style="width: 200px; font-weight:bold; text-align:center">Creación</th>
                                    <th style="width: 200px; font-weight:bold; text-align:center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $index => $value)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="text-align:center">
                                            {{-- FORMA CORRECTA con @if/@elseif --}}
                                            @if ($value->group_level == 1)
                                                Admin
                                            @elseif ($value->group_level == 2)
                                                Secretaria
                                            @elseif ($value->group_level == 3)
                                                Doctor
                                            @else
                                                {{-- Así se mostrarán los nuevos roles que crees --}}
                                                {{ ucfirst($value->slug) }}
                                            @endif
                                        </td>
                                        <td style="text-align:center">
                                            @if ($value->group_status == 1)
                                                <span class="fw-bolder p-1 rounded bg-success text-white">Activo</span>
                                            @else
                                                <span class="fw-bolder p-1 rounded bg-danger text-white">Desactivado</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center">
                                            {{-- FORMA CORRECTA con @if/@elseif --}}
                                            @if ($value->group_level == 1)
                                                <span class="user-tooltip">
                                                    {{ $admin }}
                                                    @if($admin > 0)
                                                    <div class="tooltip-content">
                                                        <strong>Administradores:</strong>
                                                        <ul>
                                                            @foreach($adminUsers as $u)
                                                                <li>{{ $u->names }} {{ $u->surnames }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                </span>
                                            @elseif ($value->group_level == 2)
                                                <span class="user-tooltip">
                                                    {{ $secretary }}
                                                    @if($secretary > 0)
                                                    <div class="tooltip-content">
                                                        <strong>Secretarias:</strong>
                                                        <ul>
                                                            @foreach($secretaryUsers as $u)
                                                                <li>{{ $u->names }} {{ $u->surnames }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                </span>
                                            @elseif ($value->group_level == 3)
                                                <span class="user-tooltip">
                                                    {{ $doctor }}
                                                    @if($doctor > 0)
                                                    <div class="tooltip-content">
                                                        <strong>Doctores:</strong>
                                                        <ul>
                                                            @foreach($doctorUsers as $u)
                                                                <li>{{ $u->names }} {{ $u->surnames }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                </span>
                                            @else
                                                {{-- Para nuevos roles, asumimos 0 por ahora, o necesitarías una lógica más compleja en el controlador --}}
                                                0
                                            @endif
                                        </td>
                                        <td style="text-align:center">{{ date('d-m-Y', strtotime($value->created_at)) }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                {{-- El resto de tus botones y modal están bien --}}
                                                <a href="{{url('admin/rol/edit/'.$value->slug)}}" class="btn btn-primary"
                                                    style="background: #7BDE7C;"><i class="fas fa-pencil-alt"></i></a>
                                                <button type="button" class="btn btn-danger" style="background: #EB5C5E;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#staticBackdrop-{{ $value->id }}">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                                <!-- Modal -->
                                                <div class="modal fade" id="staticBackdrop-{{ $value->id }}"
                                                    data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                    aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Desea
                                                                    Eliminar el Rol</h1>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Esto afectara a los usuarios que estan relacionados
                                                                directamente con este rol.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                                <a href="{{ url('admin/rol/delete/' . $value->id) }}"
                                                                    class="btn btn-danger" style="background: #EB5C5E;">
                                                                    Eliminar
                                                                </a>
                                                            </div>
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
@endsection
@push('js')
@endpush
