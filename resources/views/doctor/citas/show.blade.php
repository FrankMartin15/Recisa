@extends('Layouts.app')
@section('title', 'Atención')
@push('css')
    <!--Alertas-->
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
        
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
@endpush
@section('content')
    <div class="row mt-3 d-flex align-items-stretch">
        <div class="col-md-4 d-flex">
            <!-- Card único que contiene Especialidad + Detalles y Estado -->
            <div class="card mb-3 mb-lg-0 flex-fill">
                <div class="card-body">
                    <form action="{{ url('/doctor/attend/edit/' . $appointment->id) }}" method="post" class="w-100">
                        @csrf
                    <!-- Especialidad -->
                    <div class="text-center mb-2">
                        <p class="text-primary fw-bold mb-2">Especialidad</p>
                        <p class="text-muted">{{ $appointment->doctor->specialization->name }}</p>
                    </div>
                    
                    <hr>
                    
                    <!-- Detalles de fecha y hora -->
                    <p class="text-primary fw-bold mb-3">Detalles de la Cita</p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card mb-3 mb-lg-0">
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-3">
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                            <i class="fa-regular fa-calendar-check text-success" aria-hidden="true"></i>
                                            <input type="date" class="form-control form-control-sm text-end" name="date" value="{{ old('date', $appointment->date) }}">
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 mb-lg-0">
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-3">
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                            <i class="fa-regular fa-clock text-success" aria-hidden="true"></i>
                                            <input type="time" class="form-control form-control-sm text-end" name="time" step="60" value="{{ old('time', (isset($appointment->time) && strlen($appointment->time) >= 5) ? substr($appointment->time,0,5) : $appointment->time) }}">
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Estado de la cita -->
                    <p class="text-primary fw-bold mb-3">Estado de la Cita</p>
                    <div class="col-md-12">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle"></i> {{ implode(' ', $errors->all()) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-center mb-2">
                            <div class="row">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-stethoscope"></i></span>
                                    <select class="form-select" id="status" name="status">
                                        <option value="0" {{$appointment->status == 0 ? 'selected' : ''}}>Pendiente</option>
                                        <option value="1" {{$appointment->status == 1 ? 'selected' : ''}}>Atendido</option>
                                        <option value="2" {{$appointment->status == 2 ? 'selected' : ''}}>No asistió</option>
                                    </select>                                          
                                </div>
                                <div id="description-div" style="display: {{$appointment->status == 1 ? 'block' : 'none'}};">
                                    <label for="exampleFormControlTextarea1" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" style="text-align: left;">{{old('description', $appointment->description)}}</textarea>
                                </div>
                                <div class="col-md-12 text-center mt-3">
                                    <button type="submit" class="btn btn-primary" style="background-color: #00476D !important;">
                                        @if($appointment->status == 0)
                                            Atender
                                        @else
                                            Actualizar
                                        @endif
                                    </button>
                                </div>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 d-flex">
            <div class="card mb-1 flex-fill">
                <div class="card-header py-4">
                    <p class="text-primary m-0 fw-bold">Paciente</p>
                </div>
                <div class="card-body py-4">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-semibold">Apellidos y Nombre</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $appointment->patient->surnames }},
                                {{ $appointment->patient->names }}</p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-semibold">DNI</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $appointment->patient->dni }}</p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-semibold">Historial Clínico</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $appointment->patient->history_number }}</p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-semibold">Celular</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $appointment->patient->phone }}</p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-semibold">Edad</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $age }} años</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Historial Clínico - Ocupa todo el ancho abajo -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <p class="text-primary m-0 fw-bold">Historial Clínico</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive table" id="dataTable-2" role="grid"
                        aria-describedby="dataTable_info">
                        <table id="usuarios" class="table my-0">
                            <thead>
                                <tr style="">
                                    <th style="width: 1000px;text-align: center !important; font-weight:bold">Número del Historial</th>
                                    <th style="width: 1000px;text-align: center !important; font-weight:bold">Fecha de creación</th>
                                    <th style="width: 1000px;text-align: center !important; font-weight:bold">PDF</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clinical_histories as $clinical_history)
                                <tr>
                                    <td class="text-center">{{$clinical_history->source_pdf}}</td>
                                    <td class="text-center">{{$clinical_history->datetime_created}}</td>
                                    <td class="text-center">
                                        <a class="btn btn-primary btn-sm d-none d-sm-inline-block" role="button" target="_blank"
                                            href="{{asset('storage/clinical_histories/'.$clinical_history->source_pdf)}}"
                                            style="--bs-primary: #00486E;--bs-primary-rgb: 0,72,110;--bs-body-bg: #00476D;background: #00476D !important;">
                                            <i class="fas fa-download fa-sm text-white-50"></i>&nbsp;Ver historial
                                        </a>
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
    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
    <script>
        function toggleTextarea() {
            var selectElement = document.getElementById('status');
            var textareaDivElement = document.getElementById('description-div');
            if (selectElement.value === '2') {
                textareaDivElement.style.display = 'block';
            } else {
                textareaDivElement.style.display = 'none';
            }
        }

        window.onload = function() {
            // Inicializa el estado del div que contiene el textarea cuando se carga la página
            toggleTextarea();
            // Agrega el event listener al select para cambiar la visibilidad del div que contiene el textarea
            document.getElementById('status').addEventListener('change', toggleTextarea);
        };

        // 📄 OFFLINE PDF HANDLING
        document.addEventListener('DOMContentLoaded', function() {
            const pdfLinks = document.querySelectorAll('a[href*="clinical_histories"]');
            
            pdfLinks.forEach(link => {
                link.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const url = this.href;
                    
                    // Mostrar indicador de carga
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
                    
                    try {
                        // 1. Intentar obtener de IndexedDB
                        if (window.recisaOffline) {
                            let cachedBlob = await window.recisaOffline.getClinicalHistory(url);
                            
                            // Fallback: Intentar con ruta relativa si no encuentra la absoluta (para compatibilidad)
                            if (!cachedBlob) {
                                try {
                                    const relativeUrl = new URL(url).pathname;
                                    console.log('[PDF] Intentando fallback relativo:', relativeUrl);
                                    cachedBlob = await window.recisaOffline.getClinicalHistory(relativeUrl);
                                } catch (e) {}
                            }

                            if (cachedBlob) {
                                console.log('[PDF] Abriendo desde caché offline');
                                const blobUrl = URL.createObjectURL(cachedBlob);
                                window.open(blobUrl, '_blank');
                                this.innerHTML = originalText;
                                return;
                            }
                        }
                        
                        // 2. Si no está en caché, intentar descargar (si hay internet)
                        if (navigator.onLine) {
                            console.log('[PDF] Descargando y cacheando...');
                            const response = await fetch(url);
                            if (response.ok) {
                                const blob = await response.blob();
                                
                                // Guardar en IndexedDB para la próxima
                                if (window.recisaOffline) {
                                    await window.recisaOffline.saveClinicalHistory(url, blob);
                                }
                                
                                const blobUrl = URL.createObjectURL(blob);
                                window.open(blobUrl, '_blank');
                            } else {
                                alert('Error al descargar el archivo.');
                            }
                        } else {
                            alert('⚠️ Sin conexión y el archivo no está guardado localmente.\nURL: ' + url);
                        }
                    } catch (error) {
                        console.error('[PDF] Error:', error);
                        // Fallback: intentar abrir normal si falla todo
                        window.open(url, '_blank');
                    } finally {
                        this.innerHTML = originalText;
                    }
                });
            });
        });
        
        // Mostrar Toast de SweetAlert2 si hay mensaje de éxito
        @if(session('success'))
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
        @endif
    </script>
@endpush
