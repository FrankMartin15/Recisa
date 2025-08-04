@extends('layouts.app')
@section('title', 'Crear Usuario')
@push('css')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
    <!--Alertas-->
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-8" style="margin-top: 20px;">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Formulario del Usuario</p>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ implode(' ', $errors->all()) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-12">
                        <label for="documento" class="form-label">CONSULTA DE DNI:</label>
                        <div class="input-group mb-3">
                            <input type="text" maxlength="8" minlength="8"
                                style="border-radius:10px 0px 0px 10px !important" id="documento" class="form-control"
                                placeholder="Ingrese el DNI" aria-label="Ingrese el DNI" aria-describedby="button-addon2">
                            <button class="btn btn-primary" type="button" id="buscar"
                                style="background-color: #00476D !important;">Buscar</button>
                        </div>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dni" class="form-label">DNI:</label>
                                <input readonly class="form-control" maxlength="8" minlength="8" type="text"
                                    name="dni" id="dni" value="{{ old('dni') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="names" class="form-label">Nombres:</label>
                                <input readonly class="form-control" type="text" name="names" id="names"
                                    value="{{ old('names') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="surnames" class="form-label">Apellidos:</label>
                                <input readonly class="form-control" type="text" name="surnames" id="surnames"
                                    value="{{ old('surnames') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phone" class="form-label">Celular:</label>
                                <input class="form-control" maxlength="9" minlength="9" type="text" name="phone"
                                    id="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email:</label>
                                <input class="form-control" type="text" name="email" id="email"
                                    value="{{ old('email') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Estado:</label>
                                <select title="Estado..." data-style="btn-secondary" name="status" id="status"
                                    data-size="2" class="form-select">
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Desactivado</option>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activado</option>
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
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top: 20px;">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Foto de Perfil</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="d-flex align-items-center justify-content-center position-relative">
                            <div class="text-center">
                                <img id="avatar-img" src="https://i.postimg.cc/hjSBbZX4/doctor.png" alt="Admin"
                                    name="image" class="rounded-circle p-1 bg-primary" width="160"
                                    style="max-width: 160px; height: 160px; border-radius: 50%; object-fit: contain;">
                                <input type="file" id="avatar-input" name="image" accept="image/*"
                                    style="display: none;">
                                <label for="avatar-input" class="boton-avatar position-absolute rounded-circle bg-primary"
                                    style="width: 40px; height: 40px; bottom: -10px; left: 60%; transform: translateX(-50%); border: 2px solid white;">
                                    <i class="far fa-image text-white" style="line-height: 40px;"></i>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 text-center mt-3">
                            <button class="btn btn-primary btn-sm" type="submit"
                                style="background-color: #00476D !important;">
                                Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
@endsection

@push('js')
    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusIndicator = document.getElementById('connection-status'); // Indicador visual de conexión
            const dniField = document.getElementById('dni');
            const namesField = document.getElementById('names');
            const surnamesField = document.getElementById('surnames');
            const documentoField = document.getElementById('documento');
            const buscarButton = document.getElementById('buscar');

            async function updateConnectionStatus() {
                try {
                    if (navigator.onLine) {
                        // Verificar conexión con un recurso externo
                        await fetch('https://www.google.com', {
                            method: 'HEAD',
                            mode: 'no-cors'
                        });
                        statusIndicator.style.backgroundColor = 'green';
                        statusIndicator.title = 'Conectado a Internet';

                        // Habilitar campos de consulta
                        dniField.readOnly = true;
                        namesField.readOnly = true;
                        surnamesField.readOnly = true;
                        documentoField.disabled = false;
                        buscarButton.disabled = false;

                        // Enviar estado al servidor
                        await fetch('/update-connection-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                            },
                            body: JSON.stringify({
                                status: true
                            }),
                        });
                    } else {
                        throw new Error('Sin conexión');
                    }
                } catch (error) {
                    // Manejar estado sin conexión
                    statusIndicator.style.backgroundColor = 'red';
                    statusIndicator.title = 'Sin conexión a Internet';

                    // Deshabilitar campos de consulta
                    dniField.readOnly = false;
                    namesField.readOnly = false;
                    surnamesField.readOnly = false;
                    documentoField.disabled = true;
                    buscarButton.disabled = true;

                    // Enviar estado al servidor
                    await fetch('/update-connection-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            status: false
                        }),
                    });
                }
            }

            // Actualizar el estado de conexión al cargar la página
            updateConnectionStatus();

            // Escuchar cambios en el estado de conexión
            window.addEventListener('online', updateConnectionStatus);
            window.addEventListener('offline', updateConnectionStatus);
        });

        function buscarDNI() {
            var dni = $('#documento').val().trim();
            
            // Validar longitud del DNI
            if (dni.length !== 8) {
                showModal('El DNI debe tener exactamente 8 dígitos', 'error');
                return;
            }
            
            if (!dni) {
                showModal('Por favor, ingrese el DNI', 'error');
                return;
            }

            // Mostrar indicador de carga
            $('#buscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            
            // Limpiar campos previos
            $('#surnames').val('');
            $('#names').val('');
            $('#dni').val('');

            $.ajax({
                url: '{{ url('/admin/admin/add-consulta') }}', // Ruta para la consulta del DNI
                type: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'dni': dni
                },
                dataType: 'json',
                timeout: 15000, // 15 segundos de timeout
                success: function(response) {
                    if (response.success && response.data) {
                        // DNI encontrado exitosamente
                        var data = response.data;
                        var nombreCompleto = data.apellidoPaterno + ' ' + data.apellidoMaterno;
                        
                        $('#surnames').val(nombreCompleto);
                        $('#names').val(data.nombres);
                        $('#dni').val(data.numeroDocumento);
                        $('#documento').val('');
                        
                        // Bloquear campos para evitar edición
                        $('#surnames').prop('readonly', true);
                        $('#names').prop('readonly', true);
                        $('#dni').prop('readonly', true);
                        
                        showModal('DNI ENCONTRADO', 'success');
                    } else if (response.enable_manual) {
                        // DNI no encontrado - Activar modo manual
                        $('#dni').val(dni);
                        $('#documento').val('');
                        
                        // Habilitar campos para registro manual
                        $('#surnames').prop('readonly', false).attr('placeholder', 'Ingrese apellidos manualmente');
                        $('#names').prop('readonly', false).attr('placeholder', 'Ingrese nombres manualmente');
                        $('#dni').prop('readonly', true);
                        
                        // Enfocar el primer campo para continuar
                        $('#names').focus();
                        
                        showModal('⚠️ DNI no encontrado en las bases de datos. Complete los datos manualmente.', 'warning');
                    } else {
                        showModal('DNI no encontrado en la base de datos', 'warning');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error completo:', xhr.responseText);
                    
                    if (xhr.status === 404) {
                        // DNI no encontrado - intentar activar modo manual
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.enable_manual) {
                                $('#dni').val(dni);
                                $('#documento').val('');
                                
                                // Habilitar campos para registro manual
                                $('#surnames').prop('readonly', false).attr('placeholder', 'Ingrese apellidos manualmente');
                                $('#names').prop('readonly', false).attr('placeholder', 'Ingrese nombres manualmente');
                                $('#dni').prop('readonly', true);
                                
                                $('#names').focus();
                                showModal('⚠️ DNI no encontrado. Complete los datos manualmente.', 'warning');
                                return;
                            }
                        } catch(e) {
                            console.log('Error parsing response:', e);
                        }
                        showModal('DNI no encontrado en la base de datos de RENIEC', 'warning');
                    } else if (xhr.status === 422) {
                        showModal('DNI inválido o respuesta incorrecta del servidor', 'error');
                    } else if (xhr.status === 500) {
                        showModal('Error del servidor. Inténtelo nuevamente', 'error');
                    } else if (status === 'timeout') {
                        showModal('Tiempo de espera agotado. Verifique su conexión', 'error');
                    } else {
                        showModal('Error de conexión. Inténtelo nuevamente', 'error');
                    }
                },
                complete: function() {
                    // Restaurar botón
                    $('#buscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                }
            });
        }
        // Asociar evento click al botón #buscar
        $('#buscar').click(buscarDNI);

        // Asociar evento de teclado al campo #dni
        $('#documento').keypress(function(event) {
            // Verificar si la tecla presionada es Enter (código 13)
            if (event.which == 13) {
                buscarDNI(); // Llamar a la función de búsqueda
            }
        });

        function showModal(message, icon = "error") {
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
                icon: icon,
                title: message
            });
        }

        // Validación para nombres y apellidos: solo letras y espacios, automáticamente en mayúsculas
        $('#names, #surnames').on('input', function() {
            // Convertir a mayúsculas y permitir solo letras y espacios
            this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '');
        });

        // Validación adicional al salir del campo para limpiar espacios múltiples
        $('#names, #surnames').on('blur', function() {
            this.value = this.value.replace(/\s+/g, ' ').trim();
        });
    </script>
    <script src="{{ asset('assets/js/digitos_numericos.js') }}"></script>
    <script src="{{ asset('assets/js/manejo_carga_imagen.js') }}"></script>
    <script src="{{ asset('assets/js/mostrar_ocultar.js') }}"></script>
@endpush
