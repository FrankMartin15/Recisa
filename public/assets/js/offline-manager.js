(function () {
    'use strict';

    const DB_NAME = 'recisa-offline-db';
    const DB_VERSION = 51; // Incremented version
    const REQUEST_STORE_NAME = 'pending-requests';
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
    async function checkConnectivity() {
        if (!navigator.onLine) return false;
        try {
            const controller = new AbortController();
            setTimeout(() => controller.abort(), 3000);
            // Use a lightweight endpoint or just the home page with HEAD
            const response = await fetch('/?ping=' + Date.now(), {
                method: 'HEAD',
                signal: controller.signal,
                cache: 'no-cache'
            });
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    // --- IndexedDB ---
    async function openDb() {
        return new Promise((resolve, reject) => {
            if (db) { resolve(db); return; }

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const dbInstance = event.target.result;
                if (!dbInstance.objectStoreNames.contains(REQUEST_STORE_NAME)) {
                    const store = dbInstance.createObjectStore(REQUEST_STORE_NAME, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
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
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readonly');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const request = store.getAll();

            request.onsuccess = async () => {
                const requests = request.result;
                if (requests.length === 0) {
                    console.log('[Sync] ✅ No hay peticiones pendientes.');
                    syncInProgress = false;
                    return;
                }

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
                            } catch (e) { }
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
                    // Refresh UI if needed
                    if (window.location.pathname === '/dashboard' || window.location.pathname === '/') {
                        window.location.reload();
                    }
                }
                syncInProgress = false;
            };
        } catch (error) {
            console.error('[Sync] Error crítico:', error);
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

    // Expose API
    window.recisaOffline = {
        processForm,
        syncPendingRequests,
        checkConnectivity
    };

})();