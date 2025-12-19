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

                <!-- COMPONENTE: Datos Pendientes de Sincronización -->
                <div id="pending-sync-container" class="alert alert-info m-3 pending-sync-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Pendientes de Sincronización:</strong>
                            <span id="pending-count-badge" class="badge bg-warning text-dark ms-2">0</span>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#pending-list">
                            Ver <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse mt-3" id="pending-list">
                        <div class="list-group" id="pending-items-list">
                            <!-- Lista dinámica -->
                        </div>
                    </div>
                </div>

                <style>
                    /* Animaciones suaves para el componente de datos pendientes */
                    #pending-sync-container {
                        transition: opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease;
                        opacity: 1;
                        max-height: 500px;
                        overflow: hidden;
                    }

                    #pending-sync-container.pending-sync-hidden {
                        opacity: 0;
                        max-height: 0;
                        margin: 0 !important;
                        padding: 0 !important;
                        pointer-events: none;
                    }

                    #pending-sync-container.pending-sync-visible {
                        opacity: 1;
                        max-height: 500px;
                    }
                </style>

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
                    <form action="{{ url('/recisa/patients/add') }}" method="POST" id="patientForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dni" class="form-label">DNI:</label>
                                <input readonly class="form-control" type="text" name="dni" id="dni"
                                    maxlength="8" minlength="8" required
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
                                  <input class="form-control" maxlength="6" minlength="6" type="text"
                                      inputmode="numeric" pattern="\d{6}" autocomplete="off"
                                      name="history_number" id="history_number" value="{{ old('history_number') }}"
                                      oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);">
                            </div>
                            <div class="col-md-2">
                                <label for="phone" class="form-label">Celular:</label>
                                <input class="form-control" maxlength="9" minlength="9" type="text" name="phone"
                                    id="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Fecha Nacimiento</label>
                                <input type="date" name="date" id="date" class="form-control"
                                    max="{{ date('Y-m-d') }}" required
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

        // ⭐ FUNCIÓN PARA VERIFICAR DNI DUPLICADO
        async function checkDuplicateDNI(dni) {
            try {
                // 1. Verificar en IndexedDB (datos pendientes offline)
                const dbRequest = indexedDB.open('recisa-offline-db', 51);

                const pendingDNIs = await new Promise((resolve, reject) => {
                    dbRequest.onsuccess = (event) => {
                        const db = event.target.result;
                        const transaction = db.transaction(['pending-requests'], 'readonly');
                        const store = transaction.objectStore('pending-requests');
                        const getAllRequest = store.getAll();

                        getAllRequest.onsuccess = () => {
                            const allPending = getAllRequest.result;
                            // Filtrar solo pacientes y extraer DNIs
                            const dnis = allPending
                                .filter(req => req.url && (req.url.includes('/patients/add') || req.url.includes('/patients/insert')))
                                .map(req => req.body?.dni)
                                .filter(Boolean);
                            resolve(dnis);
                        };

                        getAllRequest.onerror = () => reject(getAllRequest.error);
                    };

                    dbRequest.onerror = () => reject(dbRequest.error);
                });

                // Verificar si el DNI ya está en pendientes
                if (pendingDNIs.includes(dni)) {
                    console.log('❌ DNI duplicado encontrado en datos pendientes:', dni);
                    return true;
                }

                // 2. Verificar en el servidor (solo si está online)
                if (navigator.onLine) {
                    try {
                        const response = await fetch(`{{ url('recisa/patients/list/json') }}?dni=${dni}`);
                        if (response.ok) {
                            const patients = await response.json();
                            const exists = patients.some(patient => patient.dni === dni);
                            if (exists) {
                                console.log('❌ DNI duplicado encontrado en servidor:', dni);
                                return true;
                            }
                        }
                    } catch (error) {
                        console.warn('No se pudo verificar DNI en servidor:', error);
                        // Continuar sin bloquear si hay error de red
                    }
                }

                return false;
            } catch (error) {
                console.error('Error al verificar DNI duplicado:', error);
                // En caso de error, permitir continuar (evitar bloqueo por error)
                return false;
            }
        }

        // 🔌 INTERCEPTAR ENVÍO DEL FORMULARIO
        $('#patientForm').on('submit', async function(e) {
            e.preventDefault(); // ✅ SIEMPRE prevenir submit por defecto

            // ⭐ VALIDACIÓN 1: DNI debe ser exactamente 8 dígitos
            const dni = $('#dni').val().trim();
            if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
                showModal('El DNI debe tener exactamente 8 dígitos numéricos', 'error');
                $('#dni').focus();
                return false;
            }

            // ⭐ VALIDACIÓN 2: Verificar DNI duplicado
            const isDuplicate = await checkDuplicateDNI(dni);
            if (isDuplicate) {
                showModal('Ya existe un paciente registrado con este DNI: ' + dni, 'error');
                $('#dni').focus();
                return false;
            }

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

        // ⭐ FUNCIÓN PARA MOSTRAR DATOS PENDIENTES DE SINCRONIZACIÓN
        async function loadPendingPatients() {
            const container = document.getElementById('pending-sync-container');
            const badge = document.getElementById('pending-count-badge');
            const list = document.getElementById('pending-items-list');

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

                        // Filtrar solo los pacientes (por URL)
                        const pendingPatients = allPending.filter(req =>
                            req.url && (req.url.includes('/patients/add') || req.url.includes('/patients/insert'))
                        );

                        if (pendingPatients.length > 0) {
                            // Mostrar container con animación suave
                            container.classList.remove('pending-sync-hidden');
                            container.classList.add('pending-sync-visible');
                            badge.textContent = pendingPatients.length;

                            // Limpiar lista
                            list.innerHTML = '';

                            // Agregar cada paciente a la lista
                            pendingPatients.forEach((req, index) => {
                                const data = req.body || {};
                                const item = document.createElement('div');
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="fas fa-user"></i> ${data.names || 'Sin nombre'} ${data.surnames || ''}</h6>
                                        <small class="text-muted">${new Date(req.timestamp).toLocaleString()}</small>
                                    </div>
                                    <p class="mb-1 small">
                                        <strong>DNI:</strong> ${data.dni || 'N/A'} |
                                        <strong>Celular:</strong> ${data.phone || 'N/A'}
                                    </p>
                                `;
                                list.appendChild(item);
                            });
                        } else {
                            // Ocultar container con animación suave
                            container.classList.remove('pending-sync-visible');
                            container.classList.add('pending-sync-hidden');
                        }
                    };
                };

                dbRequest.onerror = () => {
                    console.error('Error al abrir IndexedDB');
                };
            } catch (error) {
                console.error('Error al cargar datos pendientes:', error);
            }
        }

        // Cargar datos pendientes al iniciar
        loadPendingPatients();

        // Actualizar cada 10 segundos
        setInterval(loadPendingPatients, 10000);

        // Actualizar cuando cambie el estado de conexión
        window.addEventListener('online', loadPendingPatients);
        window.addEventListener('offline', loadPendingPatients);

    </script>
@endpush