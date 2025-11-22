@extends('layouts.app')
@section('title', 'Crear Paciente')
@push('css')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <!--Alertas-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="row" style="justify-content: center;">
        <div class="col-md-8" style="margin-top: 20px;">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Formulario del Paciente</p>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                id="auto-close-alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ implode(' ', $errors->all()) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
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
                            </div>
                        @endif
                    </div>
                    <div class="col-md-12">
                        <label for="documento" class="form-label">CONSULTA DE DNI:</label>
                        <div class="input-group mb-3">
                            <input type="text" maxlength="8" minlength="8" id="documento" class="form-control"
                                placeholder="Ingrese el DNI" aria-label="Ingrese el DNI" aria-describedby="button-addon2"
                                style="border-radius: 10px 0px 0px 10px !important">
                            <button class="btn btn-primary btn-sm" type="button" id="buscar"
                                style="background-color: #00476D !important; border:none; color: #ffff;">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <form action="{{ route('patients.store') }}" method="POST" id="patientForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dni" class="form-label">DNI:</label>
                                <input readonly class="form-control" type="text" name="dni" id="dni"
                                    value="{{ old('dni') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="names" class="form-label">Nombres:</label>
                                <input readonly class="form-control" type="text" name="names" id="names"
                                    value="{{ old('names') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="surnames" class="form-label">Apellido:</label>
                                <input readonly class="form-control" type="text" name="surnames" id="surnames"
                                    value="{{ old('surnames') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phone" class="form-label">Número Historial:</label>
                                <input class="form-control" maxlength="10" minlength="10" type="text"
                                     name="history_number" id="history_number" value="{{ old('history_number') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="phone" class="form-label">Celular:</label>
                                <input class="form-control" maxlength="9" minlength="9" type="text" name="phone"
                                    id="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Fecha Nacimiento</label>
                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ old('date') }}">
                            </div>
                            <div class="col-md-12 text-center mt-3">
                                <button class="btn btn-primary btn-sm" type="submit"
                                    style="background-color: #00476D !important;">
                                    Guardar
                                </button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        // Función para realizar la búsqueda
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

            // 🔌 VERIFICACIÓN OFFLINE
            if (!navigator.onLine) {
                console.log('Modo Offline detectado');
                $('#dni').val(dni);
                $('#documento').val('');
                
                // Habilitar campos para registro manual
                $('#surnames').prop('readonly', false).attr('placeholder', 'Ingrese apellidos manualmente');
                $('#names').prop('readonly', false).attr('placeholder', 'Ingrese nombres manualmente');
                $('#dni').prop('readonly', false); // Ahora editable
                $('#history_number').prop('readonly', false); // Asegurar que sea editable
                
                $('#names').focus();
                showModal('Modo Offline: Todos los campos habilitados', 'info');
                return;
            }

            // Mostrar indicador de carga
            $('#buscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            
            // Limpiar campos previos
            $('#surnames').val('');
            $('#names').val('');
            $('#dni').val('');

            $.ajax({
                url: '{{ url('/recisa/patients/add-consulta') }}', // Ruta para la consulta del DNI
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
                    
                    // 🔌 FALLBACK PARA ERROR DE CONEXIÓN (status 0)
                    if (xhr.status === 0 || status === 'timeout') {
                        $('#dni').val(dni);
                        $('#documento').val('');
                        
                        // Habilitar campos para registro manual
                        $('#surnames').prop('readonly', false).attr('placeholder', 'Ingrese apellidos manualmente');
                        $('#names').prop('readonly', false).attr('placeholder', 'Ingrese nombres manualmente');
                        $('#dni').prop('readonly', true);
                        
                        $('#names').focus();
                        showModal('Sin conexión a RENIEC. Ingrese datos manualmente.', 'warning');
                        return;
                    }

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

        // Función para limpiar y resetear formulario
        function limpiarFormulario() {
            $('#documento').val('');
            $('#surnames').val('').prop('readonly', false).attr('placeholder', 'Apellidos');
            $('#names').val('').prop('readonly', false).attr('placeholder', 'Nombres');
            $('#dni').val('').prop('readonly', false);
        }

        // Agregar botón de limpiar (opcional)
        $(document).ready(function() {
            // Agregar evento de doble click en cualquier campo para desbloquearlo
            $('#surnames, #names').on('dblclick', function() {
                $(this).prop('readonly', false);
                showModal('Campo desbloqueado para edición manual', 'info');
            });
        });

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
                timer: icon === 'success' ? 3000 : 4000,
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
        $('#documento,#dni,#phone,#history_number').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        // Validación para nombres y apellidos: solo letras y espacios, automáticamente en mayúsculas
        $('#names, #surnames').on('input', function() {
            // Convertir a mayúsculas y permitir solo letras y espacios
            this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '');
        });

        // Validación adicional al salir del campo para limpiar espacios múltiples
        $('#names, #surnames').on('blur', function() {
            this.value = this.value.replace(/\s+/g, ' ').trim();
        });

        // 🔌 INTERCEPTAR ENVÍO DEL FORMULARIO
        $('#patientForm').on('submit', function(e) {
            e.preventDefault(); // ✅ SIEMPRE prevenir submit por defecto

            // Usar el Offline Manager global (maneja online y offline)
            if (window.recisaOffline && typeof window.recisaOffline.processForm === 'function') {
                window.recisaOffline.processForm(e);

                // Limpiar formulario visualmente solo si offline
                if (!navigator.onLine) {
                    setTimeout(() => {
                        limpiarFormulario();
                    }, 500);
                }
            } else {
                console.error('Error: Offline Manager no cargado - El formulario no se puede enviar');
                showModal('Error: Sistema offline no disponible. Recarga la página.', 'error');
            }
        });

        // ⭐ LÓGICA GLOBAL DE ESTADO OFFLINE (NUEVO)
        function checkOfflineState() {
            if (!navigator.onLine) {
                console.log('⚡ Detectado modo offline al cargar/cambiar estado');
                // Habilitar campos inmediatamente
                $('#dni, #names, #surnames, #history_number').prop('readonly', false);
                $('#names').attr('placeholder', 'Ingrese nombres manualmente');
                $('#surnames').attr('placeholder', 'Ingrese apellidos manualmente');
                
                // Deshabilitar búsqueda
                $('#buscar').prop('disabled', true).html('<i class="fas fa-wifi-slash"></i> Offline');
                $('#documento').prop('disabled', true).attr('placeholder', 'Búsqueda no disponible offline');
                
                if (window.recisaOffline?.showAlert) {
                    window.recisaOffline.showAlert('offline', 'Modo Offline', 'Campos habilitados para registro manual.');
                }
            } else {
                // Restaurar estado online (solo si los campos están vacíos para no borrar datos ingresados)
                if ($('#names').val() === '') {
                    $('#dni, #names, #surnames').prop('readonly', true);
                }
                $('#buscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                $('#documento').prop('disabled', false).attr('placeholder', 'Ingrese el DNI');
            }
        }

        // Verificar al cargar
        checkOfflineState();

        // Listeners de conexión
        window.addEventListener('online', checkOfflineState);
        window.addEventListener('offline', checkOfflineState);

    </script>
@endpush