@extends('layouts.app')
@section('title', 'Editar Usuario')
@push('css')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
    <!--JQuery-->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-8" style="margin-top: 20px;">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Formulario del Usuario - {{ $user->names }}</p>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        @if (
                            $errors->hasAny([
                                'dni',
                                'names',
                                'surnames',
                                'phone',
                                'email',
                                'status',
                                'user_level',
                                'password',
                                'password_confirm',
                            ]))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                id="auto-close-alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ implode(' ', $errors->all()) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            <script>
                                // Después de 2 segundos (2000 ms), cierra la alerta automáticamente
                                setTimeout(function() {
                                    var alert = document.getElementById("auto-close-alert");
                                    if (alert) {
                                        var alertInstance = new bootstrap.Alert(alert);
                                        alertInstance.close();
                                    }
                                }, 3500); // 2000 milisegundos = 2 segundos
                            </script>
                        @endif
                    </div>
                    <form action="{{ url('/admin/admin/edit/' . $user->slug) }}" method="post">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dni" class="form-label">DNI:</label>
                                <input readonly class="form-control" type="text" name="dni" id="dni"
                                    value="{{ old('dni', $user->dni) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="names" class="form-label">Nombres:</label>
                                <input readonly class="form-control" type="text" name="names" id="names"
                                    value="{{ old('names', $user->names) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="surnames" class="form-label">Apellido:</label>
                                <input readonly class="form-control" type="text" name="surnames" id="surnames"
                                    value="{{ old('surnames', $user->surnames) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phone" class="form-label">Celular:</label>
                                <input class="form-control" maxlength="9" minlength="9" type="text" name="phone"
                                    id="phone" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email:</label>
                                <input class="form-control" type="text" name="email" id="email"
                                    value="{{ old('email', $user->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Estado:</label>
                                <select title="Seleccione el estado..." data-style="btn-secondary" name="status"
                                    id="status" data-size="2" class="form-select">
                                    <option value="0"
                                        {{ old('status') == '0' || $user->status == '0' ? 'selected' : '' }}>Desactivado
                                    </option>
                                    <option value="1"
                                        {{ old('status') == '1' || $user->status == '1' ? 'selected' : '' }}>Activado
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="user_level" class="form-label">Rol:</label>
                                <select title="Seleccionar" name="user_level" id="user_level" class="form-select"
                                    required>
                                    <option value="" disabled selected>Seleccionar</option>

                                    @foreach ($rol as $item)
                                        <option value="{{ $item->group_level }}"
                                            @if (isset($user) && $user->user_level == $item->group_level) selected 
                @elseif(old('user_level') == $item->group_level)
                    selected @endif>
                                            {{ ucfirst($item->slug) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control"
                                        placeholder="Password" autocomplete="off">
                                    <button id="show_password" class="btn btn-primary" onclick="mostrarPassword()"
                                        type="button" style="background-color: #00476D !important;">
                                        <span class="fa fa-eye-slash icon"></span>
                                    </button>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">* Min. 8 caracteres, números y símbolos.</small>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="password" name="password_confirm" id="password_confirm"
                                        class="form-control" placeholder="Confirmar password" autocomplete="off">
                                    <button id="show_password_confirm" class="btn btn-primary"
                                        onclick="mostrarPasswordConfirm()" type="button"
                                        style="background-color: #00476D !important;">
                                        <span class="fa fa-eye-slash icon_confirm"></span>
                                    </button>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const passwordInput = document.getElementById('password');
                                    const confirmInput = document.getElementById('password_confirm');
                                    const submitBtn = document.querySelector('button[type="submit"]');

                                    function validateComplexity(password) {
                                        // 8 chars, number, special, uppercase
                                        const hasLength = password.length >= 8;
                                        const hasNumber = /\d/.test(password);
                                        const hasSpecial = /[@$!%*?&]/.test(password);
                                        const hasUpper = /[A-Z]/.test(password);
                                        return hasLength && hasNumber && hasSpecial && hasUpper;
                                    }

                                    function updateBorder(element, isValid, isEmpty) {
                                        if (isEmpty) {
                                            element.style.borderColor = '#ced4da'; // Default Bootstrap gray
                                        } else if (isValid) {
                                            element.style.borderColor = '#198754'; // Bootstrap Success Green
                                        } else {
                                            element.style.borderColor = '#dc3545'; // Bootstrap Danger Red
                                        }
                                    }

                                    function updateFormState() {
                                        const passVal = passwordInput.value;
                                        const confirmVal = confirmInput.value;
                                        
                                        const isPassEmpty = passVal.length === 0;
                                        const isConfirmEmpty = confirmVal.length === 0;

                                        // 1. Validate Password Complexity
                                        const isComplexityValid = validateComplexity(passVal);
                                        updateBorder(passwordInput, isComplexityValid, isPassEmpty);

                                        // 2. Validate Match
                                        // Match is valid if equal AND complexity is also valid (to avoid matching two invalid passwords)
                                        const isMatchValid = (passVal === confirmVal) && isComplexityValid;
                                        // Only show red on confirm if it doesn't match OR if password itself is invalid
                                        updateBorder(confirmInput, isMatchValid, isConfirmEmpty);

                                        // 3. Button State
                                        // Enable if both empty (no change) OR both valid
                                        if (isPassEmpty && isConfirmEmpty) {
                                            submitBtn.disabled = false;
                                        } else {
                                            submitBtn.disabled = !(isComplexityValid && isMatchValid);
                                        }
                                    }

                                    passwordInput.addEventListener('input', updateFormState);
                                    confirmInput.addEventListener('input', updateFormState);
                                });
                            </script>
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary"
                                    style="background-color: #00476D !important;">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top: 20px;">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Foto de Perfil</p>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        @if ($errors->has('image'))
                            @foreach ($errors->all() as $image)
                                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                    id="auto-close-alert-image">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $image }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                <script>
                                    // Después de 2 segundos (2000 ms), cierra la alerta automáticamente
                                    setTimeout(function() {
                                        var alert = document.getElementById("auto-close-alert-image");
                                        if (alert) {
                                            var alertInstance = new bootstrap.Alert(alert);
                                            alertInstance.close();
                                        }
                                    }, 2500); // 2000 milisegundos = 2 segundos
                                </script>
                            @endforeach
                        @endif
                    </div>
                    <div class="row g-3">
                        <form action="{{ url('admin/admin/edit/photo/' . $user->slug) }}" method="post"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="d-flex align-items-center justify-content-center position-relative">
                                <div class="text-center">
                                    @if ($user->image == null)
                                        <img class="rounded-circle mb-3 mt-4"
                                            src="https://i.postimg.cc/hjSBbZX4/doctor.png" width="160" height="160">
                                    @else
                                        <img id="avatar-img" src="{{ Storage::url('public/perfiles/' . $user->image) }}"
                                            name="image" alt="{{ $user->name }}"
                                            class="rounded-circle p-1 bg-primary" width="160"
                                            style="max-width: 160px; height: 160px; border-radius: 50%; object-fit: contain;">
                                    @endif
                                    <input type="file" id="avatar-input" name="image" accept="image/*"
                                        style="display: none;">
                                    <label for="avatar-input"
                                        class="boton-avatar position-absolute rounded-circle bg-primary"
                                        style="width: 40px; height: 40px; bottom: -10px; left: 60%; transform: translateX(-50%); border: 2px solid white;">
                                        <i class="far fa-image text-white" style="line-height: 40px;"></i>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 text-center mt-3">
                                <button class="btn btn-primary btn-sm" type="submit"
                                    style="background-color: #00476D !important;">
                                    Cambiar Fotografìa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets/js/digitos_numericos.js') }}"></script>
    <script src="{{ asset('assets/js/manejo_carga_imagen.js') }}"></script>
    <script src="{{ asset('assets/js/mostrar_ocultar.js') }}"></script>
@endpush
