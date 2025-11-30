(function () {
    'use strict';

    const DB_NAME = 'recisa-offline-db';
    const DB_VERSION = 61; // Incremented to 61 to force store creation
    const REQUEST_STORE_NAME = 'pending-requests';
    const DOCTOR_APPOINTMENTS_STORE = 'doctor-appointments';
    const CLINICAL_HISTORIES_STORE = 'clinical-histories';
    const PENDING_UPLOADS_STORE = 'pending-uploads';
    let db;
    let isOnline = navigator.onLine;
    let syncInProgress = false;
    let initialized = false;

    // --- UI Helpers ---
    function showAlert(type, title, text) {
        if (typeof Swal !== 'undefined') {
            const config = {
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            };

            switch (type) {
                case 'success':
                    Swal.fire({ ...config, icon: "success", title: title || "Éxito", text: text });
                    break;
                case 'error':
                    Swal.fire({ ...config, icon: "error", title: title || "Error", text: text, timer: 4000 });
                    break;
                case 'warning':
                    Swal.fire({ ...config, icon: "warning", title: title || "Advertencia", text: text });
                    break;
                case 'info':
                    Swal.fire({ ...config, icon: "info", title: title || "Información", text: text });
                    break;
                case 'offline':
                    Swal.fire({ ...config, icon: "warning", title: "Modo Offline", text: "Sin conexión. Datos se guardarán localmente.", timer: 4000 });
                    break;
                case 'saved-locally':
                    Swal.fire({ ...config, icon: "info", title: "Guardado Localmente", text: text || "Datos guardados en dispositivo", timer: 3000 });
                    break;
                case 'sync-success':
                    Swal.fire({ ...config, icon: "success", title: "Sincronizado", text: text || "Datos sincronizados correctamente", timer: 3000 });
                    break;
                default:
                    Swal.fire({ ...config, icon: "info", title: title, text: text });
            }
        } else {
            console.log(`[${type.toUpperCase()}] ${title}: ${text || ''}`);
        }
    }

    // --- Connectivity ---
    let lastConnectivityCheck = null;
    let lastConnectivityResult = navigator.onLine;

    async function checkConnectivity() {
        if (!navigator.onLine) return false;

        // Cache del resultado por 3 segundos para evitar verificaciones excesivas
        const now = Date.now();
        if (lastConnectivityCheck && (now - lastConnectivityCheck) < 3000) {
            return lastConnectivityResult;
        }

        try {
            const controller = new AbortController();
            setTimeout(() => controller.abort(), 5000); // Aumentado a 5 segundos

            const response = await fetch('/?ping=' + Date.now(), {
                method: 'HEAD',
                signal: controller.signal,
                cache: 'no-cache'
            });

            lastConnectivityCheck = now;
            lastConnectivityResult = response.ok;
            return response.ok;
        } catch (error) {
            lastConnectivityCheck = now;
            lastConnectivityResult = false;
            return false;
        }
    }

    // --- IndexedDB ---
    async function openDb() {
        return new Promise((resolve, reject) => {
            if (db) { resolve(db); return; }

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onblocked = (event) => {
                console.warn('[IndexedDB] Database upgrade blocked.');
                // Force close connection if possible or alert user
                alert('⚠️ Actualización pendiente. Por favor cierre otras pestañas de esta aplicación y recargue.');
            };

            request.onupgradeneeded = (event) => {
                console.log('[IndexedDB] Upgrading database...');
                const dbInstance = event.target.result;
                if (!dbInstance.objectStoreNames.contains(REQUEST_STORE_NAME)) {
                    const store = dbInstance.createObjectStore(REQUEST_STORE_NAME, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                }
                if (!dbInstance.objectStoreNames.contains(DOCTOR_APPOINTMENTS_STORE)) {
                    dbInstance.createObjectStore(DOCTOR_APPOINTMENTS_STORE);
                }
                if (!dbInstance.objectStoreNames.contains(CLINICAL_HISTORIES_STORE)) {
                    dbInstance.createObjectStore(CLINICAL_HISTORIES_STORE);
                }
                if (!dbInstance.objectStoreNames.contains(PENDING_UPLOADS_STORE)) {
                    console.log('[IndexedDB] Creating pending-uploads store');
                    dbInstance.createObjectStore(PENDING_UPLOADS_STORE, { keyPath: 'id', autoIncrement: true });
                }
            };

            request.onsuccess = (event) => {
                db = event.target.result;
                resolve(db);
            };

            request.onerror = (event) => {
                console.error('IndexedDB Error:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    async function addRequestToDb(requestData) {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readwrite');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const data = { ...requestData, timestamp: new Date().toISOString() };
            const request = store.add(data);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function deleteRequestFromDb(requestId) {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readwrite');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const request = store.delete(requestId);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // --- Pending Uploads Logic ---
    async function savePendingUpload(url, formData) {
        if (!db) await openDb();

        // CRITICAL CHECK: Ensure store exists
        if (!db.objectStoreNames.contains(PENDING_UPLOADS_STORE)) {
            console.error('[Offline] Store missing. Forcing reload.');
            alert('⚠️ La base de datos interna necesita actualizarse. La página se recargará automáticamente.');
            // Delete DB to force fresh start if needed, but for now just reload
            window.location.reload();
            return Promise.reject('Store missing - reloading');
        }

        // Convert FormData to plain object for storage (Handling arrays for multiple files)
        const storedData = {};
        for (const [key, value] of formData.entries()) {
            if (storedData.hasOwnProperty(key)) {
                if (!Array.isArray(storedData[key])) {
                    storedData[key] = [storedData[key]];
                }
                storedData[key].push(value);
            } else {
                storedData[key] = value;
            }
        }

        return new Promise((resolve, reject) => {
            try {
                const transaction = db.transaction([PENDING_UPLOADS_STORE], 'readwrite');
                const store = transaction.objectStore(PENDING_UPLOADS_STORE);
                const data = {
                    url: url,
                    formData: storedData,
                    timestamp: new Date().toISOString()
                };
                const request = store.add(data);
                request.onsuccess = () => {
                    console.log('[Offline] Upload saved locally');
                    resolve(request.result);
                };
                request.onerror = () => reject(request.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    async function syncPendingUploads() {
        console.log('[Sync] ⬆️ Intentando sincronizar subidas pendientes...');
        if (!navigator.onLine) {
            console.log('[Sync] ❌ No hay conexión a internet para subidas.');
            return;
        }
        if (!db) await openDb();

        const transaction = db.transaction([PENDING_UPLOADS_STORE], 'readonly');
        const store = transaction.objectStore(PENDING_UPLOADS_STORE);
        const request = store.getAll();

        request.onsuccess = async () => {
            const uploads = request.result;
            if (uploads.length === 0) {
                console.log('[Sync] ✅ No hay subidas pendientes.');
                return;
            }

            console.log(`[Sync] 📦 Encontradas ${uploads.length} subidas pendientes.`);
            let syncedUploadsCount = 0;
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            for (const upload of uploads) {
                console.log(`[Sync] 🚀 Enviando subida ID: ${upload.id} a ${upload.url}`);
                try {
                    // Reconstruct FormData
                    const formData = new FormData();
                    for (const key in upload.formData) {
                        const value = upload.formData[key];
                        if (Array.isArray(value)) {
                            value.forEach(v => formData.append(key, v));
                        } else {
                            formData.append(key, value);
                        }
                    }
                    // Add offline sync flag
                    formData.append('_offline_sync', 'true');

                    const response = await fetch(upload.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    console.log(`[Sync] 📡 Respuesta servidor para subida: ${response.status}`);

                    if (response.ok) {
                        // Delete from DB
                        const delTx = db.transaction([PENDING_UPLOADS_STORE], 'readwrite');
                        delTx.objectStore(PENDING_UPLOADS_STORE).delete(upload.id);
                        syncedUploadsCount++;
                        console.log(`[Sync] ✅ Subida ${upload.id} sincronizada y eliminada.`);
                    } else {
                        console.error(`[Sync] ❌ Falló subida ${upload.id}:`, response.status);
                        if (response.status === 422) { // Validation Error
                            console.error('[Sync] Error de validación irreversible para subida. Eliminando.', upload.id);
                            const delTx = db.transaction([PENDING_UPLOADS_STORE], 'readwrite');
                            delTx.objectStore(PENDING_UPLOADS_STORE).delete(upload.id);
                        }
                    }
                } catch (error) {
                    console.error(`[Sync] 💥 Error de red procesando subida ${upload.id}:`, error);
                }
            }
            if (syncedUploadsCount > 0) {
                showAlert('sync-success', 'Subidas Sincronizadas', `${syncedUploadsCount} archivos subidos.`);
            }
        };
    }

    // --- Sync Logic ---
    async function syncPendingRequests() {
        console.log('[Sync] 🔄 Intentando iniciar sincronización...');

        if (syncInProgress) {
            console.log('[Sync] ⚠️ Sincronización ya en progreso.');
            return;
        }

        if (!navigator.onLine) {
            console.log('[Sync] ❌ No hay conexión a internet.');
            return;
        }

        // Double check real connectivity
        const online = await checkConnectivity();
        if (!online) {
            console.log('[Sync] ❌ Verificación de conectividad falló (ping).');
            return;
        }

        if (!db) await openDb();
        syncInProgress = true;

        try {
            // 1. Sync JSON requests
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readonly');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const request = store.getAll();

            request.onsuccess = async () => {
                const requests = request.result;
                if (requests.length === 0) {
                    console.log('[Sync] ✅ No hay peticiones pendientes.');
                } else {
                    console.log(`[Sync] 📦 Encontradas ${requests.length} peticiones pendientes.`);
                    let syncedCount = 0;

                    // Get fresh CSRF token if possible
                    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    console.log('[Sync] Token CSRF:', csrfToken ? 'Presente' : 'Faltante');

                    for (const req of requests) {
                        console.log(`[Sync] 🚀 Enviando petición ID: ${req.id} a ${req.url}`);
                        try {
                            // Update CSRF token in headers
                            if (csrfToken) {
                                req.headers['X-CSRF-TOKEN'] = csrfToken;
                            }

                            // 🔧 AGREGAR FLAG DE SINCRONIZACIÓN OFFLINE
                            // Si el body es string JSON, parsearlo, agregar flag y stringify de nuevo
                            let body = req.body;
                            if (typeof body === 'string') {
                                try {
                                    body = JSON.parse(body);
                                } catch (e) { /* Not JSON, keep as is */ }
                            }

                            // Asegurar que sea objeto para agregar la propiedad
                            if (typeof body === 'object' && body !== null) {
                                body._offline_sync = true;
                            }

                            const response = await fetch(req.url, {
                                method: req.method,
                                headers: req.headers,
                                body: JSON.stringify(body)
                            });

                            console.log(`[Sync] 📡 Respuesta servidor: ${response.status}`);

                            if (response.ok) {
                                await deleteRequestFromDb(req.id);
                                syncedCount++;
                                console.log(`[Sync] ✅ Petición ${req.id} sincronizada y eliminada.`);
                            } else {
                                const text = await response.text();
                                console.error(`[Sync] ❌ Falló petición ${req.id}. Status: ${response.status}. Body: ${text}`);

                                // Handle specific errors
                                if (response.status === 419) { // CSRF Token Mismatch
                                    console.warn('[Sync] CSRF Mismatch. Deteniendo sync para refrescar token.');
                                    break;
                                }
                                if (response.status === 422) { // Validation Error
                                    console.error('[Sync] Error de validación irreversible. Eliminando petición.', req.id);
                                    await deleteRequestFromDb(req.id);
                                }
                            }
                        } catch (err) {
                            console.error('[Sync] 💥 Error de red procesando request', req.id, err);
                        }
                    }

                    if (syncedCount > 0) {
                        showAlert('sync-success', 'Sincronización Completada', `${syncedCount} elementos enviados.`);
                        // Refresh UI if needed - reload pages that show data lists
                        const currentPath = window.location.pathname;
                        const shouldReload =
                            currentPath === '/dashboard' ||
                            currentPath === '/' ||
                            currentPath.includes('/patients/list') ||
                            currentPath.includes('/appointments/list') ||
                            currentPath.includes('/appoitnment/list') ||
                            currentPath.includes('/patients/add') ||
                            currentPath.includes('/patients/created') ||
                            currentPath.includes('/appointments/add') ||
                            currentPath.includes('/appoitnment/add');

                        if (shouldReload) {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    }
                }

                // 2. Sync Pending Uploads after JSON requests
                await syncPendingUploads();
            };
        } catch (error) {
            console.error('[Sync] Error crítico:', error);
        } finally {
            syncInProgress = false;
        }
    }

    // --- Form Handling ---
    async function processForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const requestData = {
            url: form.action,
            method: form.method.toUpperCase(),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: data
        };

        const online = await checkConnectivity();

        if (!online) {
            try {
                await addRequestToDb(requestData);
                showAlert('saved-locally', 'Guardado Offline', 'Los datos se enviarán cuando recuperes la conexión.');
                form.reset();
            } catch (e) {
                showAlert('error', 'Error', 'No se pudo guardar localmente.');
            }
            return;
        }

        // Online: Try sending
        try {
            const response = await fetch(requestData.url, {
                method: requestData.method,
                headers: requestData.headers,
                body: JSON.stringify(requestData.body)
            });

            const result = await response.json().catch(() => ({}));

            if (response.ok) {
                showAlert('success', 'Guardado', result.message || 'Operación exitosa');
                form.reset();
                if (result.redirect) {
                    setTimeout(() => window.location.href = result.redirect, 1000);
                }
            } else {
                if (response.status === 419) {
                    showAlert('error', 'Sesión Expirada', 'Por favor recarga la página.');
                } else if (response.status === 422) {
                    let msg = 'Error de validación';
                    if (result.errors) msg = Object.values(result.errors).flat().join('\n');
                    showAlert('error', 'Error', msg);
                } else {
                    // Server error -> Save offline?
                    if (response.status >= 500) {
                        await addRequestToDb(requestData);
                        showAlert('warning', 'Error del Servidor', 'Guardado localmente por error del servidor.');
                        form.reset();
                    } else {
                        showAlert('error', 'Error', result.message || 'Ocurrió un error');
                    }
                }
            }
        } catch (error) {
            // Network failed mid-request
            await addRequestToDb(requestData);
            showAlert('saved-locally', 'Conexión Interrumpida', 'Guardado localmente.');
            form.reset();
        }
    }

    // --- Initialization ---
    window.addEventListener('online', () => {
        isOnline = true;
        console.log('[Network] Online');
        setTimeout(syncPendingRequests, 1000);
    });

    window.addEventListener('offline', () => {
        isOnline = false;
        console.log('[Network] Offline');
        showAlert('offline');
    });

    window.addEventListener('DOMContentLoaded', async () => {
        await openDb();
        initialized = true;
        const online = await checkConnectivity();
        isOnline = online;
        if (online) {
            syncPendingRequests();
        } else {
            showAlert('offline');
        }
    });

    // --- Doctor Appointments Cache ---
    async function saveDoctorAppointments(data) {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([DOCTOR_APPOINTMENTS_STORE], 'readwrite');
            const store = transaction.objectStore(DOCTOR_APPOINTMENTS_STORE);
            const request = store.put(data, 'current'); // Always overwrite with latest
            request.onsuccess = () => {
                console.log('[Offline] Doctor appointments cached');
                resolve();
            };
            request.onerror = () => {
                console.error('[Offline] Error caching appointments:', request.error);
                reject(request.error);
            };
        });
    }

    async function getDoctorAppointments() {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([DOCTOR_APPOINTMENTS_STORE], 'readonly');
            const store = transaction.objectStore(DOCTOR_APPOINTMENTS_STORE);
            const request = store.get('current');
            request.onsuccess = () => {
                resolve(request.result || null);
            };
            request.onerror = () => {
                console.error('[Offline] Error retrieving appointments:', request.error);
                reject(request.error);
            };
        });
    }

    // --- Clinical History PDF Cache ---
    async function saveClinicalHistory(url, blob) {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([CLINICAL_HISTORIES_STORE], 'readwrite');
            const store = transaction.objectStore(CLINICAL_HISTORIES_STORE);
            const request = store.put(blob, url); // Key is the URL
            request.onsuccess = () => {
                console.log('[Offline] PDF cached:', url);
                resolve();
            };
            request.onerror = () => {
                console.error('[Offline] Error caching PDF:', request.error);
                reject(request.error);
            };
        });
    }

    async function getClinicalHistory(url) {
        if (!db) await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([CLINICAL_HISTORIES_STORE], 'readonly');
            const store = transaction.objectStore(CLINICAL_HISTORIES_STORE);
            const request = store.get(url);
            request.onsuccess = () => {
                resolve(request.result || null);
            };
            request.onerror = () => {
                console.error('[Offline] Error retrieving PDF:', request.error);
                reject(request.error);
            };
        });
    }

    // Expose API
    window.recisaOffline = {
        processForm,
        syncPendingRequests,
        checkConnectivity,
        saveDoctorAppointments,
        getDoctorAppointments,
        saveClinicalHistory,
        getClinicalHistory,
        savePendingUpload,
        syncPendingUploads
    };

})();