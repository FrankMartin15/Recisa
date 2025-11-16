/**
 * RECISA Sync Manager
 * Maneja la sincronización entre IndexedDB local y servidor remoto
 */

class SyncManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.isSyncing = false;
        this.syncInterval = null;
        this.retryDelay = 5000; // 5 segundos
        this.maxRetries = 3;
        this.listeners = new Map();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Inicializa el sync manager
     */
    async init() {
        // Escuchar cambios de conectividad
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Iniciar sincronización periódica si está online
        if (this.isOnline) {
            this.startPeriodicSync();
        }

        console.log('SyncManager inicializado. Estado:', this.isOnline ? 'Online' : 'Offline');
    }

    /**
     * Maneja cuando vuelve la conexión
     */
    handleOnline() {
        this.isOnline = true;
        this.emit('online');
        console.log('Conexión restaurada. Iniciando sincronización...');
        this.startPeriodicSync();
        this.syncAll();
    }

    /**
     * Maneja cuando se pierde la conexión
     */
    handleOffline() {
        this.isOnline = false;
        this.emit('offline');
        console.log('Conexión perdida. Modo offline activado.');
        this.stopPeriodicSync();
    }

    /**
     * Sistema de eventos
     */
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    emit(event, data = null) {
        if (this.listeners.has(event)) {
            this.listeners.get(event).forEach(callback => callback(data));
        }
    }

    /**
     * Sincronización periódica
     */
    startPeriodicSync(interval = 60000) { // Cada minuto
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
        }
        this.syncInterval = setInterval(() => {
            if (this.isOnline && !this.isSyncing) {
                this.syncAll();
            }
        }, interval);
    }

    stopPeriodicSync() {
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
            this.syncInterval = null;
        }
    }

    /**
     * Sincronización completa
     */
    async syncAll() {
        if (!this.isOnline || this.isSyncing) {
            console.log('Sincronización saltada:', !this.isOnline ? 'offline' : 'ya sincronizando');
            return;
        }

        this.isSyncing = true;
        this.emit('sync_start');

        try {
            // 1. Enviar cambios locales al servidor
            await this.pushLocalChanges();

            // 2. Traer cambios del servidor
            await this.pullServerChanges();

            this.emit('sync_complete');
            console.log('Sincronización completada exitosamente');
        } catch (error) {
            console.error('Error en sincronización:', error);
            this.emit('sync_error', error);
        } finally {
            this.isSyncing = false;
        }
    }

    /**
     * Envía cambios locales al servidor
     */
    async pushLocalChanges() {
        const pendingItems = await offlineDB.getPendingSyncItems();
        console.log(`Procesando ${pendingItems.length} items pendientes de sincronización`);

        for (const item of pendingItems) {
            try {
                await this.processQueueItem(item);
                await offlineDB.removeSyncItem(item.id);
                this.emit('item_synced', item);
            } catch (error) {
                console.error('Error sincronizando item:', item, error);

                // Incrementar contador de reintentos
                item.retry_count = (item.retry_count || 0) + 1;

                if (item.retry_count >= this.maxRetries) {
                    item.status = 'failed';
                    this.emit('item_failed', item);
                }

                await offlineDB.put('sync_queue', item);
            }
        }
    }

    /**
     * Procesa un item de la cola de sincronización
     */
    async processQueueItem(item) {
        const { entity, action, data, entity_id } = item;

        const endpoints = {
            patients: '/api/patients',
            appointments: '/api/appointments',
            users: '/api/users',
            clinical_histories: '/api/clinical-histories'
        };

        const baseUrl = endpoints[entity];
        if (!baseUrl) {
            throw new Error(`Entidad no soportada: ${entity}`);
        }

        let url = baseUrl;
        let method = 'POST';
        let body = data;

        switch (action) {
            case 'create':
                method = 'POST';
                break;
            case 'update':
                method = 'PUT';
                url = `${baseUrl}/${entity_id}`;
                break;
            case 'delete':
                method = 'DELETE';
                url = `${baseUrl}/${entity_id}`;
                body = null;
                break;
            default:
                throw new Error(`Acción no soportada: ${action}`);
        }

        const response = await this.fetchWithAuth(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: body ? JSON.stringify(body) : null
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        // Si es un create, actualizar el ID local con el del servidor
        if (action === 'create' && result.data && result.data.id) {
            const localData = await offlineDB.get(entity, data.id);
            if (localData) {
                // Guardar con el nuevo ID del servidor
                localData.id = result.data.id;
                localData.sync_status = 'synced';
                localData._server_id = result.data.id;
                await offlineDB.put(entity, localData);

                // Eliminar el registro con ID temporal
                if (data.id !== result.data.id) {
                    await offlineDB.delete(entity, data.id);
                }
            }
        }

        return result;
    }

    /**
     * Trae cambios del servidor
     */
    async pullServerChanges() {
        const entities = ['patients', 'appointments', 'users', 'specializations', 'user_specializations'];

        for (const entity of entities) {
            try {
                await this.pullEntity(entity);
            } catch (error) {
                console.error(`Error pulling ${entity}:`, error);
            }
        }
    }

    /**
     * Trae una entidad específica del servidor
     */
    async pullEntity(entity) {
        const lastSync = await offlineDB.getLastSyncTime(entity);

        let url = `/api/${entity.replace('_', '-')}`;
        if (lastSync) {
            url += `?updated_after=${encodeURIComponent(lastSync)}`;
        }

        try {
            const response = await this.fetchWithAuth(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            if (result.data && Array.isArray(result.data)) {
                // Marcar todos como sincronizados
                const items = result.data.map(item => ({
                    ...item,
                    sync_status: 'synced'
                }));

                await offlineDB.bulkPut(entity, items);
                await offlineDB.setLastSyncTime(entity, new Date().toISOString());

                console.log(`Sincronizados ${items.length} registros de ${entity}`);
            }
        } catch (error) {
            console.error(`Error en pullEntity(${entity}):`, error);
            // No lanzar error para continuar con otras entidades
        }
    }

    /**
     * Fetch con autenticación
     */
    async fetchWithAuth(url, options = {}) {
        const session = await offlineDB.getSession();

        if (session && session.token) {
            options.headers = {
                ...options.headers,
                'Authorization': `Bearer ${session.token}`
            };
        }

        return fetch(url, options);
    }

    /**
     * Operaciones específicas con sincronización
     */

    // Guardar paciente (local + cola de sync)
    async savePatient(patientData, isNew = true) {
        // Generar ID temporal si es nuevo
        if (isNew && !patientData.id) {
            patientData.id = this.generateTempId();
            patientData._is_temp_id = true;
        }

        patientData.sync_status = 'pending';
        await offlineDB.savePatient(patientData);

        // Agregar a cola de sincronización
        await offlineDB.addToSyncQueue(
            'patients',
            isNew ? 'create' : 'update',
            patientData,
            isNew ? null : patientData.id
        );

        // Intentar sincronizar inmediatamente si está online
        if (this.isOnline) {
            this.syncAll();
        }

        return patientData;
    }

    // Guardar cita
    async saveAppointment(appointmentData, isNew = true) {
        if (isNew && !appointmentData.id) {
            appointmentData.id = this.generateTempId();
            appointmentData._is_temp_id = true;
        }

        appointmentData.sync_status = 'pending';
        await offlineDB.saveAppointment(appointmentData);

        await offlineDB.addToSyncQueue(
            'appointments',
            isNew ? 'create' : 'update',
            appointmentData,
            isNew ? null : appointmentData.id
        );

        if (this.isOnline) {
            this.syncAll();
        }

        return appointmentData;
    }

    // Eliminar paciente
    async deletePatient(patientId) {
        // Marcar como eliminado localmente
        const patient = await offlineDB.get('patients', patientId);
        if (patient) {
            patient.sync_status = 'deleted';
            patient._deleted = true;
            await offlineDB.put('patients', patient);

            await offlineDB.addToSyncQueue(
                'patients',
                'delete',
                null,
                patientId
            );

            if (this.isOnline) {
                this.syncAll();
            }
        }
    }

    // Eliminar cita
    async deleteAppointment(appointmentId) {
        const appointment = await offlineDB.get('appointments', appointmentId);
        if (appointment) {
            appointment.sync_status = 'deleted';
            appointment._deleted = true;
            await offlineDB.put('appointments', appointment);

            await offlineDB.addToSyncQueue(
                'appointments',
                'delete',
                null,
                appointmentId
            );

            if (this.isOnline) {
                this.syncAll();
            }
        }
    }

    /**
     * Genera un ID temporal único
     */
    generateTempId() {
        return `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Verifica si hay cambios pendientes
     */
    async hasPendingChanges() {
        const pending = await offlineDB.getPendingSyncItems();
        return pending.length > 0;
    }

    /**
     * Obtiene el conteo de cambios pendientes
     */
    async getPendingCount() {
        const pending = await offlineDB.getPendingSyncItems();
        return pending.length;
    }

    /**
     * Fuerza una sincronización manual
     */
    async forceSync() {
        if (!this.isOnline) {
            throw new Error('No hay conexión a internet');
        }
        return this.syncAll();
    }

    /**
     * Limpia la cola de sincronización
     */
    async clearSyncQueue() {
        await offlineDB.clear('sync_queue');
        console.log('Cola de sincronización limpiada');
    }

    /**
     * Reinicia toda la base de datos local
     */
    async resetLocalDatabase() {
        const stores = [
            'patients', 'appointments', 'users', 'specializations',
            'user_specializations', 'clinical_histories', 'sync_queue',
            'sync_metadata'
        ];

        for (const store of stores) {
            await offlineDB.clear(store);
        }

        console.log('Base de datos local reiniciada');
        this.emit('database_reset');
    }

    /**
     * Descarga inicial de todos los datos
     */
    async initialDownload() {
        if (!this.isOnline) {
            throw new Error('Se necesita conexión para la descarga inicial');
        }

        this.emit('initial_download_start');

        try {
            // Limpiar datos existentes
            await this.resetLocalDatabase();

            // Descargar todos los datos
            await this.pullServerChanges();

            this.emit('initial_download_complete');
            console.log('Descarga inicial completada');
        } catch (error) {
            this.emit('initial_download_error', error);
            throw error;
        }
    }

    /**
     * Estado de sincronización
     */
    getStatus() {
        return {
            isOnline: this.isOnline,
            isSyncing: this.isSyncing,
            hasSyncInterval: !!this.syncInterval
        };
    }
}

// Singleton instance
const syncManager = new SyncManager();

// Exportar para uso global
window.SyncManager = SyncManager;
window.syncManager = syncManager;
