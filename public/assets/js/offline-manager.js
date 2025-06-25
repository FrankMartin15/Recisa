(function() {
    'use strict';

    const DB_NAME = 'recisa-offline-db';
    const DB_VERSION = 50;
    const REQUEST_STORE_NAME = 'pending-requests';
    let db;
    let isOnline = navigator.onLine;
    let syncInProgress = false;
    let initialized = false;

    // Función de alertas
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
                    Swal.fire({...config, icon: "success", title: title || "Éxito", text: text});
                    break;
                case 'error':
                    Swal.fire({...config, icon: "error", title: title || "Error", text: text, timer: 4000});
                    break;
                case 'warning':
                    Swal.fire({...config, icon: "warning", title: title || "Advertencia", text: text});
                    break;
                case 'info':
                    Swal.fire({...config, icon: "info", title: title || "Información", text: text});
                    break;
                case 'offline':
                    Swal.fire({...config, icon: "warning", title: "Modo Offline", text: "Sin conexión. Datos se guardarán localmente.", timer: 4000});
                    break;
                case 'saved-locally':
                    Swal.fire({...config, icon: "info", title: "Guardado Localmente", text: text || "Datos guardados en dispositivo", timer: 3000});
                    break;
                case 'sync-success':
                    Swal.fire({...config, icon: "success", title: "Sincronizado", text: text || "Datos sincronizados correctamente", timer: 3000});
                    break;
                default:
                    Swal.fire({...config, icon: "info", title: title, text: text});
            }
        } else {
            console.log(`[${type.toUpperCase()}] ${title}: ${text || ''}`);
            const message = `${title}${text ? ': ' + text : ''}`;
            alert(message);
        }
    }

    async function checkConnectivity() {
        if (!navigator.onLine) return false;

        try {
            const controller = new AbortController();
            setTimeout(() => controller.abort(), 2000);
            
            const response = await fetch('/connectivity-check', {
                method: 'GET',
                signal: controller.signal,
                cache: 'no-cache'
            });
            
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    async function openDb() {
        return new Promise((resolve, reject) => {
            if (db) {
                resolve(db);
                return;
            }
            
            console.log(`Abriendo IndexedDB: ${DB_NAME} v${DB_VERSION}`);
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                console.log(`Actualizando IndexedDB`);
                const dbInstance = event.target.result;
                
                // Eliminar store anterior si existe
                if (dbInstance.objectStoreNames.contains(REQUEST_STORE_NAME)) {
                    dbInstance.deleteObjectStore(REQUEST_STORE_NAME);
                    console.log(`Store anterior eliminado`);
                }
                
                const store = dbInstance.createObjectStore(REQUEST_STORE_NAME, { 
                    keyPath: 'id', 
                    autoIncrement: true 
                });
                
                // Agrega índices
                store.createIndex('timestamp', 'timestamp', { unique: false });
                store.createIndex('url', 'url', { unique: false });
                
                console.log(`Store recreado correctamente`);
            };

            request.onsuccess = (event) => {
                db = event.target.result;
                console.log(`IndexedDB abierta v${db.version}`);
                
                db.onversionchange = () => {
                    console.log('IndexedDB cambio de versión');
                    db.close();
                    db = null;
                };
                
                resolve(db);
            };

            request.onerror = (event) => {
                console.error('❌ Error IndexedDB:', event.target.error);
                reject(event.target.error);
            };

            request.onblocked = () => {
                console.warn('IndexedDB bloqueada');
                setTimeout(() => window.location.reload(), 2000);
            };
        });
    }

    async function addRequestToDb(requestData) {
        if (!db) await openDb();
        
        return new Promise((resolve, reject) => {
            try {
                const transaction = db.transaction([REQUEST_STORE_NAME], 'readwrite');
                const store = transaction.objectStore(REQUEST_STORE_NAME);
                
                const dataToStore = {
                    url: requestData.url,
                    method: requestData.method,
                    headers: requestData.headers,
                    body: requestData.body,
                    timestamp: new Date().toISOString(),
                    attempts: 0,
                    createdAt: new Date().toISOString()
                    // NO incluir 'id' - se genera automáticamente
                };
                
                console.log('Guardando en IndexedDB:', dataToStore);
                
                const request = store.add(dataToStore);
                
                request.onsuccess = () => {
                    console.log('Guardado en IndexedDB con ID:', request.result);
                    resolve(request.result);
                };
                
                request.onerror = (event) => {
                    console.error('Error guardando:', event.target.error);
                    reject(event.target.error);
                };
                
                transaction.onerror = (event) => {
                    console.error('Error en transacción:', event.target.error);
                    reject(event.target.error);
                };
                
            } catch (error) {
                console.error('Error en addRequestToDb:', error);
                reject(error);
            }
        });
    }

    async function deleteRequestFromDb(requestId) {
        if (!db) await openDb();
        
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readwrite');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const request = store.delete(requestId);
            
            request.onsuccess = () => {
                console.log('Eliminado de IndexedDB:', requestId);
                resolve();
            };
            
            request.onerror = (event) => {
                console.error('Error eliminando:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    async function syncPendingRequests() {
        if (syncInProgress || !navigator.onLine) return;

        if (!db) await openDb();
        syncInProgress = true;

        try {
            const transaction = db.transaction([REQUEST_STORE_NAME], 'readonly');
            const store = transaction.objectStore(REQUEST_STORE_NAME);
            const getAllRequests = store.getAll();

            getAllRequests.onsuccess = async () => {
                const requests = getAllRequests.result;
                if (requests.length === 0) {
                    syncInProgress = false;
                    return;
                }
                
                console.log(` Sincronizando ${requests.length} request(s)`);
                
                let syncedCount = 0;

                for (const req of requests) {
                    try {
                        const response = await fetch(req.url, {
                            method: req.method,
                            headers: req.headers,
                            credentials: 'same-origin',
                            body: JSON.stringify(req.body)
                        });

                        if (response.ok) {
                            await deleteRequestFromDb(req.id);
                            syncedCount++;
                        } else if ([422, 409, 404, 419].includes(response.status)) {
                            // Eliminar requests con errores no recuperables
                            await deleteRequestFromDb(req.id);
                        }
                    } catch (error) {
                        console.log('Error sincronizando:', error.message);
                    }
                }
                
                if (syncedCount > 0) {
                    showAlert('sync-success', 'Sincronización Exitosa', `${syncedCount} elemento(s) sincronizado(s)`);
                }
                
                syncInProgress = false;
            };
            
            getAllRequests.onerror = () => {
                syncInProgress = false;
            };
        } catch (error) {
            console.error('Error en sincronización:', error);
            syncInProgress = false;
        }
    }

    // FUNCIÓN PRINCIPAL - Procesamiento de formularios
    async function processForm(event) {
        event.preventDefault();
    const formElement = event.target;
        console.log('Procesando formulario...');
        
        try {
            // Extraer datos del formulario
            const formData = new FormData(formElement);
            const jsonData = {};
            formData.forEach((value, key) => { 
                jsonData[key] = value; 
            });

            // Obtener CSRF token
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenMeta) {
                throw new Error('Token CSRF no encontrado');
            }
            
            const csrfToken = csrfTokenMeta.getAttribute('content');
            
            // Preparar datos de la request
            const requestData = {
                url: formElement.action,
                method: formElement.method.toUpperCase(),
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };

            console.log('Request preparada:', requestData);

            // Verificar conectividad
            const reallyOnline = await checkConnectivity();
            console.log('Conectividad:', reallyOnline ? 'ONLINE' : 'OFFLINE');
            
            if (!reallyOnline) {
                //  SIN CONEXIÓN: Guardar localmente
                console.log('Guardando offline...');
                await addRequestToDb(requestData);
                showAlert('saved-locally', 'Guardado Sin Conexión', 
                    'Los datos se guardaron y se sincronizarán cuando haya conexión');
                resetForm(formElement);
                return;
            }

            // CON CONEXIÓN: Enviar al servidor
            console.log('Enviando al servidor...');
            
            const response = await fetch(requestData.url, {
                method: requestData.method,
                headers: requestData.headers,
                credentials: 'same-origin',
                body: JSON.stringify(requestData.body)
            });

            console.log('Respuesta:', response.status, response.ok);

            // Procesar respuesta
            let responseData;
            const responseText = await response.text();
            
            try {
                responseData = JSON.parse(responseText);
            } catch (e) {
                responseData = { message: responseText };
            }

            if (response.ok) {
                // Éxitoso
                console.log('Guardado en servidor');
                showAlert('success', 'Cita Registrada', 
                    responseData.message || 'Cita guardada correctamente');
                resetForm(formElement);
                
                // Redirección si existe
                if (responseData.redirect) {
                    setTimeout(() => {
                        window.location.href = responseData.redirect;
                    }, 1500);
                }
            } else {
                // Fallido
                console.error('Error servidor:', response.status);
                
                if (response.status >= 500) {
                    // Error servidor: guardar offline
                    await addRequestToDb(requestData);
                    showAlert('warning', 'Error del Servidor', 
                        'Error temporal. Datos guardados localmente.');
                    resetForm(formElement);
                } else if (response.status === 422) {
                    // Error validación
                    let errorMessage = 'Error en los datos ingresados';
                    if (responseData.errors) {
                        const errors = Object.values(responseData.errors).flat();
                        errorMessage = errors.join(', ');
                    } else if (responseData.message) {
                        errorMessage = responseData.message;
                    }
                    showAlert('error', 'Error de Validación', errorMessage);
                } else if (response.status === 419) {
                    // CSRF expirado
                    showAlert('error', 'Sesión Expirada', 
                        'Recarga la página e intenta nuevamente');
                } else {
                    // Otros errores
                    showAlert('error', 'Error', 
                        responseData.message || 'No se pudieron guardar los datos');
                }
            }
        } catch (error) {
            // ERROR DE RED
            console.error('Error crítico:', error);
            
            try {
                // Intentar guardar offline si es posible
                await addRequestToDb(requestData);
                showAlert('warning', 'Error de Conexión', 
                    'Sin conexión. Datos guardados localmente.');
                resetForm(formElement);
            } catch (dbError) {
                console.error('Error crítico IndexedDB:', dbError);
                showAlert('error', 'Error Crítico', 
                    'No se pudieron guardar los datos. Intenta recargar la página.');
            }
        }
    }

    function resetForm(formElement) {
        formElement.reset();
        if (typeof $ !== 'undefined' && $.fn.selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
    }

    window.addEventListener('online', () => {
        console.log('Online detectado');
        isOnline = true;
        setTimeout(() => {
            if (isOnline) syncPendingRequests();
        }, 2000);
    });

    window.addEventListener('offline', () => {
        console.log('Offline detectado');
        isOnline = false;
        showAlert('offline');
    });

    window.addEventListener('DOMContentLoaded', async () => {
        try {
            console.log('Inicializando OfflineManager...');
            
            // Intentar abrir DB
            await openDb();
            initialized = true;
            console.log('OfflineManager listo');
            
            // Verificación inicial después de delay
            setTimeout(async () => {
                const reallyOnline = await checkConnectivity();
                isOnline = reallyOnline;
                
                if (reallyOnline) {
                    console.log('Conexión disponible al inicio');
                    // Sincronizar si hay pendientes
                    setTimeout(() => syncPendingRequests(), 3000);
                } else {
                    console.log('📱 Sin conexión al inicio');
                    showAlert('offline');
                }
            }, 1000);
            
        } catch (error) {
            console.error('Error inicializando:', error);
            
            // Si hay problema con IndexedDB, limpiar
            if (error.name === 'VersionError' || error.name === 'InvalidStateError') {
                console.log('Limpiando IndexedDB corrupta...');
                
                try {
                    if (db) {
                        db.close();
                        db = null;
                    }
                    
                    const deleteRequest = indexedDB.deleteDatabase(DB_NAME);
                    deleteRequest.onsuccess = () => {
                        console.log('IndexedDB eliminada, recargando...');
                        setTimeout(() => window.location.reload(), 1000);
                    };
                    deleteRequest.onerror = () => {
                        console.log('Error eliminando DB, recargando...');
                        setTimeout(() => window.location.reload(), 1000);
                    };
                } catch (deleteError) {
                    console.error('Error eliminando DB:', deleteError);
                    setTimeout(() => window.location.reload(), 2000);
                }
            }
        }
    });

    // Verificación periódica
    setInterval(async () => {
        if (initialized) {
            const currentOnline = await checkConnectivity();
            if (currentOnline !== isOnline) {
                isOnline = currentOnline;
                if (isOnline) {
                    setTimeout(() => syncPendingRequests(), 1000);
                }
            }
        }
    }, 120000);

    // API
    window.recisaOffline = {
        processForm: processForm,
        syncPendingRequests: syncPendingRequests,
        openDb: openDb,
        showAlert: showAlert,
        checkConnectivity: checkConnectivity,
        
        // Funciones de debugging
        getStoredRequests: async () => {
            if (!db) await openDb();
            return new Promise((resolve) => {
                const transaction = db.transaction([REQUEST_STORE_NAME], 'readonly');
                const store = transaction.objectStore(REQUEST_STORE_NAME);
                const getAllRequests = store.getAll();
                getAllRequests.onsuccess = () => resolve(getAllRequests.result);
                getAllRequests.onerror = () => resolve([]);
            });
        },
        
        clearStoredRequests: async () => {
            if (!db) await openDb();
            return new Promise((resolve) => {
                const transaction = db.transaction([REQUEST_STORE_NAME], 'readwrite');
                const store = transaction.objectStore(REQUEST_STORE_NAME);
                const clearRequest = store.clear();
                clearRequest.onsuccess = () => {
                    console.log('Requests pendientes eliminados');
                    resolve();
                };
                clearRequest.onerror = () => resolve();
            });
        },
        
        // Estado del sistema
        isReady: () => initialized,
        isOnline: () => isOnline,
        getDbVersion: () => DB_VERSION
    };

    console.log('OfflineManager cargado - Versión 50');

})();