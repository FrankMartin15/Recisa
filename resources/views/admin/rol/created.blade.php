@extends('layouts.app')
@section('title','Crear Rol')
    @push('css')
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
    @endpush

    @section('content')      
        <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Formulario de Rol</p>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ url('admin/rol/add') }}" method="post">
                        @csrf
                        <div class="row g-3 justify-content-center">

                            <!-- CAMPO 1: Nombre del Rol (ahora es un select) -->
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Nombre del Rol:</label>
                                <select id="slug" name="slug" class="form-select" required>
                                    <option value="" disabled selected>Seleccione un rol</option>
                                    <option value="admin" {{ old('slug') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="doctor" {{ old('slug') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                    <option value="secretaria" {{ old('slug') == 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                                </select>
                            </div>

                            <!-- CAMPO 2: Estado (Sin cambios) -->
                            <div class="col-md-6">
                                <label for="group_status" class="form-label">Estado:</label>
                                <select name="group_status" id="group_status" class="form-select" required>
                                    <option value="" disabled selected>Seleccione una opción</option>
                                    <option value="1" {{ old('group_status') == '1' ? 'selected' : '' }}>Activado</option> 
                                    <option value="0" {{ old('group_status') == '0' ? 'selected' : '' }}>Desactivado</option>
                                </select>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #00476D !important;">Guardar Rol</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> 
    @endsection

    @push('js')
        <!-- Latest compiled and minified JavaScript -->
        <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                // Cuando cambia el valor del campo select
                $('#group_level').on('change', function () {
                    var selectedValue = $(this).val(); // Obtén el valor seleccionado
                    var slugValue = ''; // Valor para el campo de texto
                    // Asigna un valor al campo de texto según la selección
                    if (selectedValue == '1') {
                        slugValue = 'admin';
                    } else if (selectedValue == '2') {
                        slugValue = 'secretaria';
                    } else if (selectedValue == '3') {
                        slugValue = 'doctor';
                    }
                    // Establece el valor del campo de texto
                    $('#slug').val(slugValue);
                });
            });
            </script>
    @endpush