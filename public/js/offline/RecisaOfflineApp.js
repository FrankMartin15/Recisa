/**
 * RECISA Offline Application
 * Aplicación principal que integra IndexedDB, SyncManager y UI
 */

class RecisaOfflineApp {
    constructor() {
        this.isInitialized = false;
        this.currentUser = null;
        this.uiElements = {};
    }

    /**
     * Inicializa toda la aplicación offline
     */
    async init() {
        try {
            console.log('Inicializando RECISA Offline App...');

            // 1. Inicializar IndexedDB
            await offlineDB.init();
            console.log('IndexedDB lista');

            // 2. Inicializar SyncManager
            await syncManager.init();
            console.log('SyncManager listo');

            // 3. Cargar sesión guardada
            await this.loadSession();

            // 4. Crear UI de estado
            this.createStatusUI();

            // 5. Configurar listeners
            this.setupEventListeners();

            // 6. Si está online y autenticado, sincronizar
            if (navigator.onLine && this.currentUser) {
                this.showStatus('Sincronizando datos...', 'info');
                await this.performInitialSync();
            }

            this.isInitialized = true;
            console.log('RECISA Offline App inicializada correctamente');

            // Notificar
            if (!navigator.onLine) {
                this.showStatus('Modo Offline - Los datos se guardarán localmente', 'warning');
            } else {
                this.showStatus('Conectado - Datos sincronizados', 'success');
            }

        } catch (error) {
            console.error('Error inicializando app offline:', error);
            this.showStatus('Error inicializando modo offline', 'danger');
        }
    }

    /**
     * Carga la sesión guardada en IndexedDB
     */
    async loadSession() {
        const session = await offlineDB.getSession();
        if (session && session.user) {
            this.currentUser = session.user;
            console.log('Sesión cargada:', this.currentUser.names);
        }
    }

    /**
     * Guarda la sesión en IndexedDB
     */
    async saveSession(userData, token = null) {
        this.currentUser = userData;
        await offlineDB.saveSession({
            user: userData,
            token: token,
            saved_at: new Date().toISOString()
        });
        console.log('Sesión guardada en IndexedDB');
    }

    /**
     * Realiza sincronización inicial
     */
    async performInitialSync() {
        try {
            const response = await fetch('/api/initial-data', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            if (!response.ok) {
                throw new Error('Error obteniendo datos iniciales');
            }

            const result = await response.json();

            if (result.success && result.data) {
                // Guardar todos los datos en IndexedDB
                const data = result.data;

                // Guardar usuario actual
                if (data.user) {
                    await this.saveSession(data.user);
                }

                // Guardar pacientes
                if (data.patients) {
                    for (const patient of data.patients) {
                        patient.sync_status = 'synced';
                        await offlineDB.savePatient(patient);
                    }
                }

                // Guardar citas
                if (data.appointments) {
                    for (const apt of data.appointments) {
                        apt.sync_status = 'synced';
                        await offlineDB.saveAppointment(apt);
                    }
                }

                // Guardar usuarios
                if (data.users) {
                    for (const user of data.users) {
                        user.sync_status = 'synced';
                        await offlineDB.saveUser(user);
                    }
                }

                // Guardar especializaciones
                if (data.specializations) {
                    await offlineDB.bulkPut('specializations', data.specializations);
                }

                // Guardar user_specializations
                if (data.user_specializations) {
                    await offlineDB.bulkPut('user_specializations', data.user_specializations);
                }

                // Guardar user_groups
                if (data.user_groups) {
                    await offlineDB.bulkPut('user_groups', data.user_groups);
                }

                console.log('Sincronización inicial completada');
                this.showStatus('Datos sincronizados correctamente', 'success');
            }
        } catch (error) {
            console.error('Error en sincronización inicial:', error);
            this.showStatus('Error sincronizando. Usando datos locales.', 'warning');
        }
    }

    /**
     * Crea la UI de estado offline
     */
    createStatusUI() {
        // Crear contenedor de estado
        const statusBar = document.createElement('div');
        statusBar.id = 'offline-status-bar';
        statusBar.innerHTML = `
            <div class="offline-status-container">
                <span id="offline-indicator" class="badge bg-success">
                    <i class="fas fa-wifi"></i> Online
                </span>
                <span id="sync-status" class="badge bg-info ms-2" style="display:none;">
                    <i class="fas fa-sync fa-spin"></i> Sincronizando...
                </span>
                <span id="pending-count" class="badge bg-warning ms-2" style="display:none;">
                    <i class="fas fa-clock"></i> <span class="count">0</span> pendientes
                </span>
                <button id="force-sync-btn" class="btn btn-sm btn-outline-primary ms-2" style="display:none;">
                    <i class="fas fa-sync"></i> Sincronizar
                </button>
            </div>
        `;

        // Estilos
        const style = document.createElement('style');
        style.textContent = `
            #offline-status-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255,255,255,0.95);
                border-top: 1px solid #ddd;
                padding: 8px 15px;
                z-index: 9999;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }
            .offline-status-container {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }
            #offline-notification {
                position: fixed;
                top: 60px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            }
            .offline-mode #offline-indicator {
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        // Insertar al final del body
        document.body.appendChild(statusBar);

        // Crear área de notificaciones
        const notifArea = document.createElement('div');
        notifArea.id = 'offline-notification';
        document.body.appendChild(notifArea);

        // Guardar referencias
        this.uiElements = {
            statusBar: statusBar,
            indicator: document.getElementById('offline-indicator'),
            syncStatus: document.getElementById('sync-status'),
            pendingCount: document.getElementById('pending-count'),
            forceSyncBtn: document.getElementById('force-sync-btn'),
            notification: notifArea
        };

        // Actualizar estado inicial
        this.updateConnectionStatus();
    }

    /**
     * Configura event listeners
     */
    setupEventListeners() {
        // Cambios de conexión
        syncManager.on('online', () => this.updateConnectionStatus());
        syncManager.on('offline', () => this.updateConnectionStatus());

        // Sincronización
        syncManager.on('sync_start', () => {
            this.uiElements.syncStatus.style.display = 'inline-block';
        });

        syncManager.on('sync_complete', () => {
            this.uiElements.syncStatus.style.display = 'none';
            this.updatePendingCount();
            this.showStatus('Sincronización completada', 'success');
        });

        syncManager.on('sync_error', (error) => {
            this.uiElements.syncStatus.style.display = 'none';
            this.showStatus('Error en sincronización: ' + error.message, 'danger');
        });

        syncManager.on('item_synced', () => {
            this.updatePendingCount();
        });

        // Botón de sincronización manual
        this.uiElements.forceSyncBtn.addEventListener('click', () => {
            this.forceSync();
        });

        // Interceptar formularios
        this.interceptForms();

        // Actualizar contador de pendientes periódicamente
        setInterval(() => this.updatePendingCount(), 30000);
        this.updatePendingCount();
    }

    /**
     * Actualiza el indicador de conexión
     */
    updateConnectionStatus() {
        const indicator = this.uiElements.indicator;

        if (navigator.onLine) {
            indicator.className = 'badge bg-success';
            indicator.innerHTML = '<i class="fas fa-wifi"></i> Online';
            document.body.classList.remove('offline-mode');
            this.uiElements.forceSyncBtn.style.display = 'inline-block';
        } else {
            indicator.className = 'badge bg-danger';
            indicator.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline';
            document.body.classList.add('offline-mode');
            this.uiElements.forceSyncBtn.style.display = 'none';
        }
    }

    /**
     * Actualiza el contador de items pendientes
     */
    async updatePendingCount() {
        const count = await syncManager.getPendingCount();
        const badge = this.uiElements.pendingCount;

        if (count > 0) {
            badge.style.display = 'inline-block';
            badge.querySelector('.count').textContent = count;
        } else {
            badge.style.display = 'none';
        }
    }

    /**
     * Muestra una notificación de estado
     */
    showStatus(message, type = 'info', duration = 3000) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        this.uiElements.notification.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, duration);
    }

    /**
     * Fuerza sincronización manual
     */
    async forceSync() {
        if (!navigator.onLine) {
            this.showStatus('No hay conexión a internet', 'warning');
            return;
        }

        try {
            this.showStatus('Sincronizando...', 'info');
            await syncManager.forceSync();
        } catch (error) {
            this.showStatus('Error: ' + error.message, 'danger');
        }
    }

    /**
     * Obtiene el token CSRF
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Intercepta formularios para guardar offline
     */
    interceptForms() {
        // Interceptar formularios de pacientes
        document.addEventListener('submit', async (e) => {
            const form = e.target;

            // Formulario de nuevo paciente
            if (form.id === 'patient-form' || form.action.includes('/patients/add')) {
                if (!navigator.onLine) {
                    e.preventDefault();
                    await this.handleOfflinePatientForm(form);
                }
            }

            // Formulario de nueva cita
            if (form.id === 'appointment-form' || form.action.includes('/appoitnment/add')) {
                if (!navigator.onLine) {
                    e.preventDefault();
                    await this.handleOfflineAppointmentForm(form);
                }
            }
        });
    }

    /**
     * Maneja formulario de paciente offline
     */
    async handleOfflinePatientForm(form) {
        const formData = new FormData(form);
        const patientData = {
            dni: formData.get('dni'),
            names: formData.get('names'),
            surnames: formData.get('surnames'),
            phone: formData.get('phone'),
            date: formData.get('date'),
            history_number: formData.get('history_number') || 'HC-OFFLINE-' + Date.now()
        };

        try {
            await syncManager.savePatient(patientData, true);
            this.showStatus('Paciente guardado localmente. Se sincronizará cuando haya conexión.', 'success');

            // Limpiar formulario
            form.reset();

            // Redirigir a lista si existe
            if (window.location.href.includes('/add')) {
                setTimeout(() => {
                    window.location.href = window.location.href.replace('/add', '/list');
                }, 1500);
            }
        } catch (error) {
            this.showStatus('Error guardando paciente: ' + error.message, 'danger');
        }
    }

    /**
     * Maneja formulario de cita offline
     */
    async handleOfflineAppointmentForm(form) {
        const formData = new FormData(form);
        const appointmentData = {
            id_quota: formData.get('id_quota'),
            id_patient: formData.get('id_patient'),
            date: formData.get('date'),
            time: formData.get('time'),
            description: formData.get('description') || '',
            status: 0
        };

        try {
            await syncManager.saveAppointment(appointmentData, true);
            this.showStatus('Cita guardada localmente. Se sincronizará cuando haya conexión.', 'success');

            // Limpiar formulario
            form.reset();

            // Redirigir a lista si existe
            if (window.location.href.includes('/add')) {
                setTimeout(() => {
                    window.location.href = window.location.href.replace('/add', '/list');
                }, 1500);
            }
        } catch (error) {
            this.showStatus('Error guardando cita: ' + error.message, 'danger');
        }
    }

    /**
     * Carga datos de pacientes para mostrar offline
     */
    async loadPatientsOffline() {
        const patients = await offlineDB.getAllPatients();
        return patients.filter(p => !p._deleted);
    }

    /**
     * Carga datos de citas para mostrar offline
     */
    async loadAppointmentsOffline() {
        const appointments = await offlineDB.getAllAppointments();
        return appointments.filter(a => !a._deleted);
    }

    /**
     * Carga datos de doctores para mostrar offline
     */
    async loadDoctorsOffline() {
        return await offlineDB.getDoctors();
    }

    /**
     * Buscar paciente por DNI offline
     */
    async searchPatientByDNI(dni) {
        return await offlineDB.getPatientByDNI(dni);
    }

    /**
     * Obtiene estadísticas del dashboard offline
     */
    async getDashboardStatsOffline() {
        return await offlineDB.getDashboardStats();
    }

    /**
     * Verifica si hay datos locales
     */
    async hasLocalData() {
        const patientCount = await offlineDB.count('patients');
        return patientCount > 0;
    }
}

// Crear instancia global
const recisaApp = new RecisaOfflineApp();

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    recisaApp.init();
});

// Exportar para uso global
window.RecisaOfflineApp = RecisaOfflineApp;
window.recisaApp = recisaApp;
