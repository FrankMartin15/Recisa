/**
 * RECISA Offline Database - IndexedDB Wrapper
 * Maneja toda la persistencia local de datos
 */

class OfflineDatabase {
    constructor() {
        this.dbName = 'recisa_offline_db';
        this.dbVersion = 1;
        this.db = null;
        this.isReady = false;
    }

    /**
     * Inicializa la base de datos IndexedDB
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = (event) => {
                console.error('Error abriendo IndexedDB:', event.target.error);
                reject(event.target.error);
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                this.isReady = true;
                console.log('IndexedDB inicializada correctamente');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                this.createStores(db);
            };
        });
    }

    /**
     * Crea todos los object stores (tablas)
     */
    createStores(db) {
        // Pacientes
        if (!db.objectStoreNames.contains('patients')) {
            const patientsStore = db.createObjectStore('patients', { keyPath: 'id' });
            patientsStore.createIndex('dni', 'dni', { unique: true });
            patientsStore.createIndex('names', 'names', { unique: false });
            patientsStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Citas/Appointments
        if (!db.objectStoreNames.contains('appointments')) {
            const appointmentsStore = db.createObjectStore('appointments', { keyPath: 'id' });
            appointmentsStore.createIndex('id_patient', 'id_patient', { unique: false });
            appointmentsStore.createIndex('date', 'date', { unique: false });
            appointmentsStore.createIndex('status', 'status', { unique: false });
            appointmentsStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Usuarios (Doctores, Admins, Secretarias)
        if (!db.objectStoreNames.contains('users')) {
            const usersStore = db.createObjectStore('users', { keyPath: 'id' });
            usersStore.createIndex('dni', 'dni', { unique: true });
            usersStore.createIndex('user_level', 'user_level', { unique: false });
            usersStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Especializaciones
        if (!db.objectStoreNames.contains('specializations')) {
            const specializationsStore = db.createObjectStore('specializations', { keyPath: 'id' });
            specializationsStore.createIndex('title', 'title', { unique: false });
            specializationsStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // User Specializations (Asignaciones de doctores)
        if (!db.objectStoreNames.contains('user_specializations')) {
            const userSpecStore = db.createObjectStore('user_specializations', { keyPath: 'id' });
            userSpecStore.createIndex('id_user', 'id_user', { unique: false });
            userSpecStore.createIndex('id_specialization', 'id_specialization', { unique: false });
            userSpecStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Historias Clínicas (metadata)
        if (!db.objectStoreNames.contains('clinical_histories')) {
            const clinicalStore = db.createObjectStore('clinical_histories', { keyPath: 'id' });
            clinicalStore.createIndex('id_patient', 'id_patient', { unique: false });
            clinicalStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Grupos de Usuario (Roles)
        if (!db.objectStoreNames.contains('user_groups')) {
            const groupsStore = db.createObjectStore('user_groups', { keyPath: 'id' });
            groupsStore.createIndex('group_level', 'group_level', { unique: true });
        }

        // Cola de sincronización
        if (!db.objectStoreNames.contains('sync_queue')) {
            const syncStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
            syncStore.createIndex('entity', 'entity', { unique: false });
            syncStore.createIndex('action', 'action', { unique: false });
            syncStore.createIndex('created_at', 'created_at', { unique: false });
            syncStore.createIndex('status', 'status', { unique: false });
        }

        // Sesión de usuario actual
        if (!db.objectStoreNames.contains('session')) {
            db.createObjectStore('session', { keyPath: 'key' });
        }

        // Metadata de sincronización
        if (!db.objectStoreNames.contains('sync_metadata')) {
            db.createObjectStore('sync_metadata', { keyPath: 'key' });
        }

        console.log('Object stores creados correctamente');
    }

    /**
     * Operaciones CRUD genéricas
     */

    // CREATE/UPDATE
    async put(storeName, data) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);

            // Agregar timestamp de modificación local
            data._local_updated_at = new Date().toISOString();

            const request = store.put(data);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // READ by ID
    async get(storeName, id) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(id);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // READ ALL
    async getAll(storeName) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // READ by Index
    async getByIndex(storeName, indexName, value) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const index = store.index(indexName);
            const request = index.getAll(value);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // DELETE
    async delete(storeName, id) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(id);

            request.onsuccess = () => resolve(true);
            request.onerror = () => reject(request.error);
        });
    }

    // CLEAR store
    async clear(storeName) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.clear();

            request.onsuccess = () => resolve(true);
            request.onerror = () => reject(request.error);
        });
    }

    // COUNT
    async count(storeName) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.count();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Operaciones específicas para RECISA
     */

    // Pacientes
    async savePatient(patient) {
        patient.sync_status = patient.sync_status || 'pending';
        return this.put('patients', patient);
    }

    async getPatientByDNI(dni) {
        const patients = await this.getByIndex('patients', 'dni', dni);
        return patients.length > 0 ? patients[0] : null;
    }

    async getAllPatients() {
        return this.getAll('patients');
    }

    async searchPatients(query) {
        const allPatients = await this.getAllPatients();
        const lowerQuery = query.toLowerCase();
        return allPatients.filter(p =>
            p.dni.includes(query) ||
            p.names.toLowerCase().includes(lowerQuery) ||
            p.surnames.toLowerCase().includes(lowerQuery)
        );
    }

    // Citas
    async saveAppointment(appointment) {
        appointment.sync_status = appointment.sync_status || 'pending';
        return this.put('appointments', appointment);
    }

    async getAppointmentsByPatient(patientId) {
        return this.getByIndex('appointments', 'id_patient', patientId);
    }

    async getAppointmentsByDate(date) {
        return this.getByIndex('appointments', 'date', date);
    }

    async getAllAppointments() {
        return this.getAll('appointments');
    }

    // Usuarios/Doctores
    async saveUser(user) {
        user.sync_status = user.sync_status || 'synced';
        return this.put('users', user);
    }

    async getUsersByLevel(level) {
        return this.getByIndex('users', 'user_level', level);
    }

    async getDoctors() {
        return this.getUsersByLevel(3); // Level 3 = Doctor
    }

    async getAllUsers() {
        return this.getAll('users');
    }

    // Especializaciones
    async saveSpecialization(spec) {
        spec.sync_status = spec.sync_status || 'synced';
        return this.put('specializations', spec);
    }

    async getAllSpecializations() {
        return this.getAll('specializations');
    }

    // User Specializations
    async saveUserSpecialization(userSpec) {
        userSpec.sync_status = userSpec.sync_status || 'synced';
        return this.put('user_specializations', userSpec);
    }

    async getUserSpecializations(userId) {
        return this.getByIndex('user_specializations', 'id_user', userId);
    }

    async getAllUserSpecializations() {
        return this.getAll('user_specializations');
    }

    // Historias Clínicas
    async saveClinicalHistory(history) {
        history.sync_status = history.sync_status || 'pending';
        return this.put('clinical_histories', history);
    }

    async getClinicalHistoriesByPatient(patientId) {
        return this.getByIndex('clinical_histories', 'id_patient', patientId);
    }

    // Sesión
    async saveSession(sessionData) {
        return this.put('session', { key: 'current_user', ...sessionData });
    }

    async getSession() {
        return this.get('session', 'current_user');
    }

    async clearSession() {
        return this.delete('session', 'current_user');
    }

    // Sync Queue
    async addToSyncQueue(entity, action, data, entityId = null) {
        const queueItem = {
            entity: entity,
            action: action,
            data: data,
            entity_id: entityId,
            created_at: new Date().toISOString(),
            status: 'pending',
            retry_count: 0
        };
        return this.put('sync_queue', queueItem);
    }

    async getPendingSyncItems() {
        return this.getByIndex('sync_queue', 'status', 'pending');
    }

    async updateSyncItemStatus(id, status) {
        const item = await this.get('sync_queue', id);
        if (item) {
            item.status = status;
            item.last_attempt = new Date().toISOString();
            return this.put('sync_queue', item);
        }
    }

    async removeSyncItem(id) {
        return this.delete('sync_queue', id);
    }

    // Sync Metadata
    async setLastSyncTime(entity, time) {
        return this.put('sync_metadata', {
            key: `last_sync_${entity}`,
            time: time
        });
    }

    async getLastSyncTime(entity) {
        const data = await this.get('sync_metadata', `last_sync_${entity}`);
        return data ? data.time : null;
    }

    /**
     * Bulk operations para sincronización
     */
    async bulkPut(storeName, items) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);

            let completed = 0;
            const total = items.length;

            if (total === 0) {
                resolve(0);
                return;
            }

            items.forEach(item => {
                item._local_updated_at = new Date().toISOString();
                const request = store.put(item);
                request.onsuccess = () => {
                    completed++;
                    if (completed === total) {
                        resolve(completed);
                    }
                };
                request.onerror = () => reject(request.error);
            });
        });
    }

    /**
     * Estadísticas del dashboard
     */
    async getDashboardStats() {
        const [patients, appointments, users] = await Promise.all([
            this.count('patients'),
            this.count('appointments'),
            this.count('users')
        ]);

        const doctors = await this.getDoctors();
        const pendingSync = await this.getPendingSyncItems();

        return {
            total_patients: patients,
            total_appointments: appointments,
            total_users: users,
            total_doctors: doctors.length,
            pending_sync: pendingSync.length
        };
    }

    /**
     * Cerrar la base de datos
     */
    close() {
        if (this.db) {
            this.db.close();
            this.db = null;
            this.isReady = false;
        }
    }
}

// Singleton instance
const offlineDB = new OfflineDatabase();

// Exportar para uso global
window.OfflineDatabase = OfflineDatabase;
window.offlineDB = offlineDB;
