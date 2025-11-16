/**
 * RECISA Offline Data Loader
 * Carga datos desde IndexedDB cuando está offline y los muestra en las vistas
 */

class OfflineDataLoader {
    constructor() {
        this.isOffline = !navigator.onLine;
    }

    /**
     * Inicializa el loader según la página actual
     */
    async init() {
        // Esperar a que IndexedDB esté lista
        await this.waitForDB();

        const path = window.location.pathname;

        // Detectar qué página es y cargar datos correspondientes
        if (path.includes('/patients/list')) {
            await this.loadPatientsTable();
        } else if (path.includes('/appoitnment/list') || path.includes('/appointment/list')) {
            await this.loadAppointmentsTable();
        } else if (path.includes('/dashboard')) {
            await this.loadDashboardStats();
        } else if (path.includes('/patients/add')) {
            this.setupOfflinePatientForm();
        } else if (path.includes('/appoitnment/add') || path.includes('/appointment/add')) {
            await this.loadDoctorsForAppointment();
        }

        console.log('OfflineDataLoader inicializado para:', path);
    }

    /**
     * Espera a que IndexedDB esté lista
     */
    async waitForDB() {
        return new Promise((resolve) => {
            const checkDB = () => {
                if (window.offlineDB && window.offlineDB.isReady) {
                    resolve();
                } else {
                    setTimeout(checkDB, 100);
                }
            };
            checkDB();
        });
    }

    /**
     * Carga la tabla de pacientes con datos offline
     */
    async loadPatientsTable() {
        if (!this.isOffline) {
            // Si está online, verificar si hay datos para mostrar mientras carga
            const hasLocal = await offlineDB.count('patients');
            if (hasLocal === 0) return; // Dejar que la página cargue normalmente
        }

        try {
            const patients = await offlineDB.getAllPatients();
            const filteredPatients = patients.filter(p => !p._deleted);

            // Buscar la tabla de pacientes
            const table = document.querySelector('table') || document.querySelector('#example');
            if (!table) {
                console.log('Tabla de pacientes no encontrada');
                return;
            }

            // Crear el tbody con los datos offline
            let tbody = table.querySelector('tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                table.appendChild(tbody);
            }

            // Limpiar contenido existente si está offline
            if (this.isOffline) {
                tbody.innerHTML = '';

                // Mostrar indicador offline
                this.showOfflineIndicator('Mostrando datos locales - ' + filteredPatients.length + ' pacientes');
            }

            // Agregar filas
            filteredPatients.forEach((patient, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${patient.dni || 'N/A'}</td>
                    <td>${patient.names || ''}</td>
                    <td>${patient.surnames || ''}</td>
                    <td>${patient.phone || ''}</td>
                    <td>${this.formatDate(patient.date)}</td>
                    <td>${patient.history_number || 'N/A'}</td>
                    <td>
                        ${patient.sync_status === 'pending' ?
                            '<span class="badge bg-warning">Pendiente sync</span>' :
                            '<span class="badge bg-success">Sincronizado</span>'}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="offlineLoader.viewPatient(${patient.id})" ${this.isOffline ? '' : 'disabled'}>
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="offlineLoader.editPatient(${patient.id})" ${this.isOffline ? '' : 'disabled'}>
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Reinicializar DataTable si existe
            if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
                $(table).DataTable().destroy();
            }

            // Inicializar DataTable
            if ($.fn.DataTable) {
                $(table).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    }
                });
            }

        } catch (error) {
            console.error('Error cargando pacientes offline:', error);
        }
    }

    /**
     * Carga la tabla de citas con datos offline
     */
    async loadAppointmentsTable() {
        if (!this.isOffline) return;

        try {
            const appointments = await offlineDB.getAllAppointments();
            const filteredAppointments = appointments.filter(a => !a._deleted);

            const table = document.querySelector('table');
            if (!table) return;

            let tbody = table.querySelector('tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                table.appendChild(tbody);
            }

            tbody.innerHTML = '';
            this.showOfflineIndicator('Mostrando citas locales - ' + filteredAppointments.length + ' citas');

            for (const appointment of filteredAppointments) {
                // Obtener datos relacionados
                const patient = await offlineDB.get('patients', appointment.id_patient);
                const doctorSpec = await offlineDB.get('user_specializations', appointment.id_quota);
                let doctorName = 'N/A';
                let specialization = 'N/A';

                if (doctorSpec) {
                    const doctor = await offlineDB.get('users', doctorSpec.id_user);
                    const spec = await offlineDB.get('specializations', doctorSpec.id_specialization);
                    if (doctor) doctorName = `${doctor.names} ${doctor.surnames}`;
                    if (spec) specialization = spec.title;
                }

                const statusBadge = this.getStatusBadge(appointment.status);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${appointment.id}</td>
                    <td>${patient ? `${patient.names} ${patient.surnames}` : 'N/A'}</td>
                    <td>${doctorName}</td>
                    <td>${specialization}</td>
                    <td>${this.formatDate(appointment.date)}</td>
                    <td>${appointment.time}</td>
                    <td>${statusBadge}</td>
                    <td>
                        ${appointment.sync_status === 'pending' ?
                            '<span class="badge bg-warning">Pendiente</span>' : ''}
                    </td>
                `;
                tbody.appendChild(row);
            }

        } catch (error) {
            console.error('Error cargando citas offline:', error);
        }
    }

    /**
     * Carga estadísticas del dashboard
     */
    async loadDashboardStats() {
        if (!this.isOffline) return;

        try {
            const stats = await offlineDB.getDashboardStats();

            // Buscar y actualizar los contadores
            const counters = document.querySelectorAll('.card-body h5, .card-body .display-4');

            // Intentar encontrar elementos por texto
            const textMappings = {
                'pacientes': stats.total_patients,
                'citas': stats.total_appointments,
                'doctores': stats.total_doctors,
                'usuarios': stats.total_users
            };

            counters.forEach(counter => {
                const parentText = counter.closest('.card')?.textContent.toLowerCase() || '';
                for (const [key, value] of Object.entries(textMappings)) {
                    if (parentText.includes(key)) {
                        counter.textContent = value;
                        break;
                    }
                }
            });

            this.showOfflineIndicator('Dashboard con datos locales');

            // Mostrar pendientes de sync
            if (stats.pending_sync > 0) {
                this.showPendingSyncAlert(stats.pending_sync);
            }

        } catch (error) {
            console.error('Error cargando dashboard offline:', error);
        }
    }

    /**
     * Carga doctores para el formulario de citas
     */
    async loadDoctorsForAppointment() {
        try {
            const userSpecs = await offlineDB.getAllUserSpecializations();
            const select = document.querySelector('select[name="id_quota"]');

            if (!select || !this.isOffline) return;

            // Limpiar opciones existentes
            select.innerHTML = '<option value="">Seleccionar Doctor</option>';

            for (const spec of userSpecs) {
                const doctor = await offlineDB.get('users', spec.id_user);
                const specialization = await offlineDB.get('specializations', spec.id_specialization);

                if (doctor && specialization) {
                    const option = document.createElement('option');
                    option.value = spec.id;
                    option.textContent = `${doctor.names} ${doctor.surnames} - ${specialization.title}`;
                    select.appendChild(option);
                }
            }

            this.showOfflineIndicator('Formulario en modo offline');

        } catch (error) {
            console.error('Error cargando doctores:', error);
        }
    }

    /**
     * Configura el formulario de pacientes para modo offline
     */
    setupOfflinePatientForm() {
        if (!this.isOffline) return;

        const form = document.querySelector('form');
        if (!form) return;

        this.showOfflineIndicator('Formulario en modo offline - Los datos se guardarán localmente');

        // Agregar badge de estado
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Localmente';
            submitBtn.classList.add('btn-warning');
        }
    }

    /**
     * Ver detalles de paciente offline
     */
    async viewPatient(id) {
        const patient = await offlineDB.get('patients', id);
        if (patient) {
            alert(`
Paciente: ${patient.names} ${patient.surnames}
DNI: ${patient.dni}
Teléfono: ${patient.phone}
Fecha Nac.: ${this.formatDate(patient.date)}
Historia: ${patient.history_number}
Estado: ${patient.sync_status}
            `);
        }
    }

    /**
     * Editar paciente offline
     */
    async editPatient(id) {
        const patient = await offlineDB.get('patients', id);
        if (patient) {
            const newPhone = prompt('Nuevo teléfono:', patient.phone);
            if (newPhone && newPhone !== patient.phone) {
                patient.phone = newPhone;
                await syncManager.savePatient(patient, false);
                alert('Paciente actualizado. Se sincronizará cuando haya conexión.');
                location.reload();
            }
        }
    }

    /**
     * Muestra indicador de modo offline
     */
    showOfflineIndicator(message) {
        // Remover indicador existente
        const existing = document.getElementById('offline-data-indicator');
        if (existing) existing.remove();

        const indicator = document.createElement('div');
        indicator.id = 'offline-data-indicator';
        indicator.className = 'alert alert-warning alert-dismissible fade show';
        indicator.style.cssText = 'position: sticky; top: 0; z-index: 1000; margin: 0;';
        indicator.innerHTML = `
            <strong><i class="fas fa-wifi-slash"></i> Modo Offline</strong> - ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(indicator, container.firstChild);
        }
    }

    /**
     * Muestra alerta de operaciones pendientes
     */
    showPendingSyncAlert(count) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-info';
        alert.innerHTML = `
            <i class="fas fa-sync"></i>
            Tienes <strong>${count}</strong> operaciones pendientes de sincronización.
            Se enviarán automáticamente cuando vuelva la conexión.
        `;

        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alert, container.firstChild);
        }
    }

    /**
     * Formatea fecha
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-PE');
        } catch (e) {
            return dateString;
        }
    }

    /**
     * Obtiene badge de estado de cita
     */
    getStatusBadge(status) {
        const badges = {
            0: '<span class="badge bg-warning">Pendiente</span>',
            1: '<span class="badge bg-success">Atendida</span>',
            2: '<span class="badge bg-danger">Cancelada</span>'
        };
        return badges[status] || badges[0];
    }

    /**
     * Buscar paciente por DNI
     */
    async searchPatientByDNI(dni) {
        return await offlineDB.getPatientByDNI(dni);
    }
}

// Crear instancia global
const offlineLoader = new OfflineDataLoader();

// Inicializar cuando la app offline esté lista
document.addEventListener('DOMContentLoaded', () => {
    // Esperar un poco para que se inicialice la app principal
    setTimeout(() => {
        offlineLoader.init();
    }, 500);
});

// Escuchar cambios de conexión
window.addEventListener('online', () => {
    // Recargar si vuelve la conexión
    const indicator = document.getElementById('offline-data-indicator');
    if (indicator) {
        indicator.className = 'alert alert-success';
        indicator.innerHTML = '<strong>Conexión restaurada!</strong> Recargando datos del servidor...';
        setTimeout(() => location.reload(), 2000);
    }
});

window.addEventListener('offline', () => {
    offlineLoader.init();
});

// Exportar
window.OfflineDataLoader = OfflineDataLoader;
window.offlineLoader = offlineLoader;
