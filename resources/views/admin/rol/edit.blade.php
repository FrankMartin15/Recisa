@extends('layouts.app')
@section('title', 'Editar Rol')
@push('css')
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Error!</strong> {{ implode(', ', $errors->all()) }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- La acción del formulario debe apuntar a la ruta de actualización --}}
                    <form action="{{ route('admin.rol.update', $usergroup) }}" method="post">
                        @csrf
                        <div class="row g-3 justify-content-center">

                            <!-- CAMPO 1: Nombre del Rol (Editable para roles nuevos, solo lectura para principales) -->
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Nombre del Rol:</label>
                                <input type="text" id="slug" name="slug" class="form-control"
                                    value="{{ old('slug', $usergroup->slug) }}" {{-- Hacemos que los roles principales no se puedan renombrar para seguridad --}}
                                    @if (in_array($usergroup->group_level, [1, 2, 3])) readonly @endif>
                                @if (in_array($usergroup->group_level, [1, 2, 3]))
                                    <small class="form-text text-muted">El nombre de los roles principales no se puede
                                        cambiar.</small>
                                @endif
                            </div>

                            <!-- CAMPO 2: Estado del Rol -->
                            <div class="col-md-6">
                                <label for="group_status" class="form-label">Estado:</label>
                                <select name="group_status" id="group_status" class="form-select" required>
                                    <option value="1"
                                        {{ old('group_status', $usergroup->group_status) == 1 ? 'selected' : '' }}>Activado
                                    </option>
                                    <option value="0"
                                        {{ old('group_status', $usergroup->group_status) == 0 ? 'selected' : '' }}>
                                        Desactivado</option>
                                </select>
                            </div>

                            {{-- Enviamos el nivel de forma oculta para no perderlo --}}
                            <input type="hidden" name="group_level" value="{{ $usergroup->group_level }}">

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary"
                                    style="background-color: #00476D !important;">Actualizar Rol</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@endpush
