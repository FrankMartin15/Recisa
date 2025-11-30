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

        {{-- 📡 OFFLINE MODE INDICATOR --}}
        <div id="offline-mode-indicator" class="alert alert-warning shadow mt-3" style="display: none;">
            <i class="fas fa-wifi-slash"></i> <strong>Modo Offline:</strong> Estás viendo datos guardados. Las acciones se sincronizarán cuando vuelva la conexión.
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

            // 📡 LÓGICA DE ASISTENCIA OFFLINE
            document.addEventListener('DOMContentLoaded', function() {
                const offlineIndicator = document.getElementById('offline-mode-indicator');
                const tableBody = document.querySelector('#especialidades tbody');

                // Mostrar/ocultar indicador y cargar datos offline
                async function toggleOfflineMode() {
                    if (!navigator.onLine) {
                        offlineIndicator.style.display = 'block';
                        await renderOfflineTable();
                    } else {
                        offlineIndicator.style.display = 'none';
                        // Si vuelve online, recargar para tener datos frescos del servidor
                        // pero solo si estábamos offline antes (para evitar recargas infinitas)
                        if(offlineIndicator.dataset.wasOffline === 'true'){
                             window.location.reload();
                        }
                    }
                    offlineIndicator.dataset.wasOffline = !navigator.onLine;
                }

                // Renderizar tabla desde IndexedDB
                async function renderOfflineTable() {
                    if (!window.recisaOffline) return;

                    try {
                        const data = await window.recisaOffline.getDoctorAppointments();
                        if (data && data.appointments) {
                            console.log('[Doctor Offline] Rendering table with', data.appointments.length, 'appointments');
                            
                            // Limpiar tabla actual (que podría estar vacía o con error)
                            // Nota: DataTables puede complicar esto, así que destruimos y recreamos si es necesario
                            // Por simplicidad, manipulamos el DOM directo si DataTables falla o para asegurar visualización
                            
                            let html = '';
                            data.appointments.forEach((appt, index) => {
                                html += `
                                    <tr>
                                        <td style="text-align: left">${index + 1}</td>
                                        <td style="text-align: left">${appt.patient.names} ${appt.patient.surnames}</td>
                                        <td style="text-align: left">${appt.doctor.specialization.name}</td>
                                        <td style="text-align: left">${appt.date}</td>
                                        <td style="text-align: left">${appt.time}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="/doctor/attend/edit/${appt.id}" class="btn btn-primary" style="background: #F4D03F !important;">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            // Si DataTables está activo, usar su API, sino HTML directo
                            if ($.fn.DataTable.isDataTable('#especialidades')) {
                                $('#especialidades').DataTable().destroy();
                            }
                            tableBody.innerHTML = html;
                            // Re-inicializar DataTables (opcional, o dejar como tabla simple offline)
                        } else {
                            console.log('[Doctor Offline] No cached appointments found');
                            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay datos offline disponibles. Conéctate para sincronizar.</td></tr>';
                        }
                    } catch (err) {
                        console.error('[Doctor Offline] Error rendering table:', err);
                    }
                }

                // Listeners
                window.addEventListener('online', toggleOfflineMode);
                window.addEventListener('offline', toggleOfflineMode);
                
                // Check inicial
                toggleOfflineMode();
            });
            
            // 📥 AUTOMATIC REAL-TIME SYNC
            document.addEventListener('DOMContentLoaded', function() {
                let lastDataHash = ''; // Para detectar cambios

                // Función principal de sincronización
                async function syncOfflineData() {
                    if (!navigator.onLine || !window.recisaOffline) return;

                    try {
                        // 1. Fetch JSON data with cache busting
                        const response = await fetch('/doctor/citas/list/json?t=' + Date.now(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            
                            // Simple hash detection (JSON stringify)
                            const currentHash = JSON.stringify(data.appointments);
                            const hasChanges = currentHash !== lastDataHash;
                            
                            if (hasChanges) {
                                console.log('[Doctor] New data detected, syncing...');
                                await window.recisaOffline.saveDoctorAppointments(data);
                                lastDataHash = currentHash;

                                // 2. Prefetch detail pages (HTML) and PDFs
                                if (data.appointments && data.appointments.length > 0) {
                                    const promises = [];
                                    let newContentDownloaded = false;

                                    // A. Detail Pages (HTML)
                                    data.appointments.forEach(appt => {
                                        const url = `/doctor/attend/edit/${appt.id}`;
                                        promises.push(fetch(url, { priority: 'low' }).catch(() => {}));
                                        
                                        // B. Clinical History PDFs
                                        if (appt.patient && appt.patient.clinical_histories) {
                                            appt.patient.clinical_histories.forEach(hist => {
                                                // Use the authoritative URL from backend if available, otherwise fallback
                                                let pdfUrl = hist.full_url || `/storage/${hist.source_pdf}`;
                                                
                                                // CRITICAL: Normalize to absolute URL to match show.blade.php (this.href)
                                                try {
                                                    pdfUrl = new URL(pdfUrl, window.location.origin).href;
                                                } catch (e) {
                                                    console.error('Invalid URL:', pdfUrl);
                                                }
                                                
                                                if (pdfUrl) {
                                                    promises.push(async () => {
                                                        try {
                                                            // Check cache first
                                                            const cached = await window.recisaOffline.getClinicalHistory(pdfUrl);
                                                            if (!cached) {
                                                                console.log('[Doctor] Downloading new PDF:', pdfUrl);
                                                                const resp = await fetch(pdfUrl, { priority: 'low' });
                                                                if (resp.ok) {
                                                                    const blob = await resp.blob();
                                                                    await window.recisaOffline.saveClinicalHistory(pdfUrl, blob);
                                                                    newContentDownloaded = true;
                                                                } else {
                                                                    console.error('[Doctor] Failed to download PDF:', pdfUrl, resp.status);
                                                                }
                                                            }
                                                        } catch (e) { console.error('PDF error', e); }
                                                    });
                                                }
                                            });
                                        }
                                    });
                                    
                                    // Execute all
                                    const finalPromises = promises.map(p => typeof p === 'function' ? p() : p);
                                    await Promise.allSettled(finalPromises);

                                    // Notify only if something new was actually downloaded or list changed significantly
                                    if (newContentDownloaded || hasChanges) {
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: "top-end",
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true
                                        });
                                        Toast.fire({ icon: "success", title: "Datos sincronizados automáticamente" });
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.error('[Doctor] Auto-sync error:', error);
                    }
                }

                // Initial sync
                syncOfflineData();

                // Poll every 15 seconds
                setInterval(syncOfflineData, 15000);
            });
        </script>    
    @endpush