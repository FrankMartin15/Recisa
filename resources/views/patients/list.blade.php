@extends('layouts.app')
@section('title', 'Pacientes')
@push('css')
    <!--Alertas-->
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <!--CSS TABLA-->
    <link href="{{ asset('assets/css/dataTables.bootstrap5.css') }}" rel="stylesheet" />
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
    @if (session('error'))
        <script>
            let errorMessage = "{{ session('error') }}";
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
        <a class="btn btn-primary btn-sm d-none d-sm-inline-block" target="_blank" role="button"
            href="{{ url('recisa/patients/reporte') }}"
            style="--bs-primary: #00486E;--bs-primary-rgb: 0,72,110;--bs-body-bg: #00476D;background: #00476D !important;">
            <i class="fas fa-download fa-sm text-white-50"></i>&nbsp;Generar Reporte
        </a>
    </div>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Lista de Pacientes</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive table" id="dataTable-2" role="grid" aria-describedby="dataTable_info">
                        <table id="pacientes" class="table my-0">
                            <thead>
                                <tr>
                                    <th style="width: 100px; font-weight:bold; text-align: left;">DNI</th>
                                    <th style="width: 400px; font-weight:bold; text-align: left;">Paciente</th>
                                    <th style="width: 300px; font-weight:bold; text-align: left;">Número Historial</th>
                                    <th style="width: 100px; font-weight:bold; text-align: left;">Celular</th>
                                    <th style="width: 300px; font-weight:bold; text-align: left;">Edad</th>
                                    <th style="width: 300px; font-weight:bold; text-align: left;">Creación</th>
                                    <th style="width: 300px; text-align: center !important; font-weight:bold;">Opciones</th>
                                    <th style="width: 300px; text-align: center !important; font-weight:bold;">Reporte</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patients as $patient)
                                    <tr>
                                        <td style="text-align: left;">{{ $patient->dni }}</td>
                                        <td style="text-align: left;">{{ $patient->names }}, {{ $patient->surnames }}</td>
                                        <td style="text-align: left;">{{ $patient->history_number }}</td>
                                        <td style="text-align: left;">{{ $patient->phone }}</td>
                                        <td style="text-align: left;">
                                            @if($patient->calculated_age === 'N/A')
                                                -
                                            @else
                                                {{ $patient->calculated_age }} años
                                            @endif
                                        </td>
                                        <td>{{ date('d-m-Y', strtotime($patient->created_at)) }}</td>
                                        <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ url('recisa/patients/edit/' . $patient->slug) }}"
                                                        class="btn btn-primary" style="background: #7BDE7C;"><i
                                                            class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-info manage-history-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#historyModal"
                                                        data-patient-id="{{ $patient->id }}"
                                                        data-patient-name="{{ $patient->names }} {{ $patient->surnames }}"
                                                        data-history-number="{{ $patient->history_number ?? '' }}">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger" style="background: #EB5C5E;"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdrop-{{ $patient->id }}">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="staticBackdrop-{{ $patient->id }}"
                                                        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                        aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Desea
                                                                        Eliminar el Paciente</h1>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    El paciente {{ $patient->names }} debe ser informado
                                                                    después de haber realizado esta acción.
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancelar</button>
                                                                    <a href="{{ url('recisa/patients/delete/' . $patient->id) }}"
                                                                        class="btn btn-danger"style="background: #EB5C5E;">
                                                                        Eliminar
                                                                    </a>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        <td class="text-center">
                                            <a href="{{ url('recisa/patients/reporte/' . $patient->dni) }}" target="_blank"
                                                class="btn btn-primary" style="background: #58D68D !important;"><i
                                                    class="fa-solid fa-file-pdf"></i></a>
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

    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Gestionar Historial de: <span id="modalPatientName" class="fw-bold text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- El action se establecerá dinámicamente con JavaScript --}}
                <form id="historyForm" method="post" enctype="multipart/form-data">
                    @csrf
                    {{-- @method('PUT') Usamos PUT para actualizar --}}
                    
                    <input type="hidden" name="patient_id" id="modalPatientId">

                    <div class="row">
                        <!-- Columna Izquierda: Datos y subida de archivos -->
                        <div class="col-md-5">
                            <h6 class="text-muted fw-bold mb-3">Datos del Paciente</h6>
                            <div class="mb-3">
                                <label for="modalHistoryNumber" class="form-label">Número de Historial Clínico</label>
                                <input type="text" class="form-control" id="modalHistoryNumber" name="history_number"
                                    maxlength="6" minlength="6" inputmode="numeric" pattern="\d{6}" autocomplete="off"
                                    placeholder="Ingrese el número de historial" required>
                            </div>
                            
                            <hr>
                            
                            <h6 class="text-muted fw-bold mb-3">Subir Nuevos Documentos</h6>
                            {{-- TU DISEÑO DE SUBIDA DE ARCHIVOS --}}
                            <div class="container_files">
                                <input type="file" id="file-input" name="files[]" accept=".pdf,.jpg,.jpeg,.png" multiple />
                                <label for="file-input" class="label">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                      Seleccione archivos a subir(Max. 3Mb)
                                </label>
                                <div id="num-of-files">No ha seleccionado ningún archivo</div>
                                <ul id="files-list"></ul>
                            </div>
                        </div>

                        <!-- Columna Derecha: Lista de documentos existentes -->
                        <div class="col-md-7">
                            <h6 class="text-muted fw-bold mb-3">Documentos Existentes</h6>
                            <div id="existingFilesList" class="list-group" style="max-height: 400px; overflow-y: auto;">
                                {{-- La lista de archivos se cargará aquí con AJAX --}}
                                <p class="text-center text-muted">Cargando documentos...</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="historyForm" class="btn btn-primary" style="background-color: #00476D !important;">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
function showAlert(type, title, text) {
    if (typeof Swal !== 'undefined') {
        const config = {
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        };

        switch(type) {
            case 'success':
                Swal.fire({...config, icon: "success", title: title, text: text});
                break;
            case 'error':
                Swal.fire({...config, icon: "error", title: title, text: text, timer: 4000});
                break;
            default:
                Swal.fire({...config, icon: "info", title: title, text: text});
        }
    } else {
        alert(`${title}: ${text}`);
    }
}

// ✅ MOVER ESTA FUNCIÓN AL SCOPE GLOBAL
function loadExistingFiles(patientId) {
    const fileListContainer = document.getElementById('existingFilesList');
    fileListContainer.innerHTML = '<p class="text-center text-muted">Cargando documentos...</p>';
    
    fetch(`{{ url('recisa/patients/get-files') }}/${patientId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Respuesta del servidor:', data);
            fileListContainer.innerHTML = '';
            
            if (data.success && data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    const fileItem = `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-pdf text-danger me-2"></i> ${file.name}</span>
                            <div>
                                <a href="${file.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFile(event, ${file.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    fileListContainer.innerHTML += fileItem;
                });
            } else {
                fileListContainer.innerHTML = '<p class="text-center text-muted">No hay documentos para este paciente.</p>';
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar archivos:', error);
            fileListContainer.innerHTML = `<p class="text-center text-danger">Error: ${error.message}</p>`;
        });
}

// ✅ FUNCIÓN DELETEFILE CORREGIDA - TAMBIÉN EN SCOPE GLOBAL
function deleteFile(event, fileId) {
    event.preventDefault();
    
    // ✅ CAMBIO: SweetAlert en lugar de confirm() básico
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Solo ejecutar si el usuario confirma
            console.log('🗑️ Eliminando archivo:', fileId);
            
            // Mostrar loading mientras se elimina
            Swal.fire({
                title: 'Eliminando archivo...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`{{ url('recisa/files/delete') }}/${fileId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('📡 Respuesta del servidor:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('✅ Respuesta exitosa:', data);
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Recargar la lista de archivos
                    const patientId = document.getElementById('modalPatientId').value;
                    loadExistingFiles(patientId);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar el archivo'
                    });
                }
            })
            .catch(error => {
                console.error('❌ Error completo:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'Ocurrió un problema al conectar con el servidor: ' + error.message
                });
            });
        }
    });
}

// ✅ DATATABLE CONFIGURATION
$('#pacientes').DataTable({
    responsive: true,
    autoWidth: false,
    "language": {
        "lengthMenu": "Mostrar " +
            `<select class="custom-select custom-select-sm w-50 form-select form-select-sm mb-2">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </select>`,
        "zeroRecords": "No se encontró nada - lo siento",
        "info": "Mostrando la página _PAGE_ de _PAGES_ de _TOTAL_ pacientes",
        "infoEmpty": "No hay registros disponibles",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "search": "Buscar:",
        "paginate": {
            "next": ">",
            "previous": "<"
        }
    }
});

// ✅ EVENT LISTENERS EN DOM CONTENT LOADED
document.addEventListener('DOMContentLoaded', function() {
    const historyModal = document.getElementById('historyModal');
    
    historyModal.addEventListener('show.bs.modal', function(event) {
        // Botón que activó el modal
        const button = event.relatedTarget;
        
        // Extraer datos del botón (data-* attributes)
        const patientId = button.getAttribute('data-patient-id');
        const patientName = button.getAttribute('data-patient-name');
        const historyNumber = button.getAttribute('data-history-number');
        
        // Poblar los campos del modal
        const modalTitle = historyModal.querySelector('#modalPatientName');
        const modalPatientIdInput = historyModal.querySelector('#modalPatientId');
        const modalHistoryNumberInput = historyModal.querySelector('#modalHistoryNumber');
        const historyForm = historyModal.querySelector('#historyForm');
        
        modalTitle.textContent = patientName;
        modalPatientIdInput.value = patientId;
        modalHistoryNumberInput.value = historyNumber;
        
        // Establecer la URL del action del formulario dinámicamente
        historyForm.action = `{{ url('recisa/patients/history') }}/${patientId}`;
        
        // Cargar los archivos existentes del paciente
        loadExistingFiles(patientId);
    });

    // File input preview functionality
    const fileInput = document.getElementById('file-input');
    const num_of_files = document.getElementById("num-of-files");
    const files_list = document.getElementById("files-list");

    fileInput.addEventListener("change", () => {
        files_list.innerHTML = "";
        num_of_files.textContent = `${fileInput.files.length} Archivos Seleccionados`;
        for (const file of fileInput.files) {
            let reader = new FileReader();
            let listItem = document.createElement("li");
            let fileName = file.name;
            let fileSize = (file.size / 1024).toFixed(1);
            listItem.innerHTML = `<p>${fileName}</p><p>${fileSize}KB</p>`;
            if (fileSize >= 1024) {
                fileSize = (fileSize / 1024).toFixed(1);
                listItem.innerHTML = `<p>${fileName}</p><p>${fileSize}MB</p>`;
            }
            files_list.appendChild(listItem);
        }
    });

    // Form submit handler
    document.getElementById('historyForm').addEventListener('submit', async function(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const url = form.action;

        // 1. OFFLINE HANDLING
        if (!navigator.onLine && window.recisaOffline) {
            try {
                await window.recisaOffline.savePendingUpload(url, formData);
                Swal.fire({
                    icon: 'info',
                    title: 'Guardado Offline',
                    text: 'El documento se subirá automáticamente cuando recuperes la conexión.',
                    timer: 3000,
                    showConfirmButton: false
                });
                
                // Close modal
                const historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                historyModal.hide();
                
                // Clear inputs
                form.reset();
                document.getElementById("files-list").innerHTML = "";
                document.getElementById("num-of-files").textContent = "No ha seleccionado ningún archivo";
                
            } catch (error) {
                console.error('Offline save error:', error);
                Swal.fire('Error', 'No se pudo guardar localmente.', 'error');
            }
            return;
        }

        // 2. ONLINE HANDLING
        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor, espere.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Cerramos el modal
                const historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                historyModal.hide();
                
                // Clear inputs
                form.reset();
                document.getElementById("files-list").innerHTML = "";
                document.getElementById("num-of-files").textContent = "No ha seleccionado ningún archivo";

            } else {
                // Manejar errores de validación u otros
                let errorText = data.message;
                if (data.errors) {
                    errorText += '<br>' + Object.values(data.errors).join('<br>');
                }
                Swal.fire('Error', errorText, 'error');
            }
        })
        .catch(async error => {
            console.error('Error al enviar el formulario:', error);
            
            // FALLBACK: Si falla la conexión, intentar guardar offline
            if (window.recisaOffline) {
                try {
                    console.log('Intentando guardar offline tras error de red...');
                    await window.recisaOffline.savePendingUpload(url, formData);
                    Swal.fire({
                        icon: 'info',
                        title: 'Guardado Offline',
                        text: 'Hubo un error de conexión, pero el archivo se guardó localmente y se subirá después.',
                        timer: 4000
                    });
                    
                    // Close modal & reset
                    const historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                    historyModal.hide();
                    form.reset();
                    document.getElementById("files-list").innerHTML = "";
                    document.getElementById("num-of-files").textContent = "No ha seleccionado ningún archivo";
                    return;
                } catch (offlineError) {
                    console.error('Error al guardar offline:', offlineError);
                    Swal.fire('Error', 'No se pudo guardar localmente: ' + (offlineError.message || offlineError), 'error');
                }
            }
            
            Swal.fire('Error de Red', 'No se pudo conectar con el servidor y no se pudo guardar localmente.', 'error');
        });
    });
});

// ✅ MOVER ESTA FUNCIÓN AL SCOPE GLOBAL
function loadExistingFiles(patientId) {
    const fileListContainer = document.getElementById('existingFilesList');
    fileListContainer.innerHTML = '<p class="text-center text-muted">Cargando documentos...</p>';
    
    // Offline Check
    if (!navigator.onLine) {
        fileListContainer.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-wifi-slash"></i><br>
                Modo Offline<br>
                <small>No se pueden ver documentos antiguos sin conexión, pero puedes subir nuevos.</small>
            </div>
        `;
        return;
    }
    
    fetch(`{{ url('recisa/patients/get-files') }}/${patientId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Respuesta del servidor:', data);
            fileListContainer.innerHTML = '';
            
            if (data.success && data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    const fileItem = `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-pdf text-danger me-2"></i> ${file.name}</span>
                            <div>
                                <a href="${file.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFile(event, ${file.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    fileListContainer.innerHTML += fileItem;
                });
            } else {
                fileListContainer.innerHTML = '<p class="text-center text-muted">No hay documentos para este paciente.</p>';
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar archivos:', error);
            fileListContainer.innerHTML = `<p class="text-center text-danger">Error: ${error.message}</p>`;
        });
}
</script>
@endpush
