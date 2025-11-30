<nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top">
    <div class="container-fluid">
        <form class="d-none d-sm-inline-block me-auto ms-md-3 my-2 my-md-0 mw-100 navbar-search"
            style="position: relative;">
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <button class="btn btn-primary py-0" type="button"
                        style="background: #135578 !important;height: 54px;">
                        <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path fill="#ffffff"
                                d="M128 0c17.7 0 32 14.3 32 32l0 32 128 0 0-32c0-17.7 14.3-32 32-32s32 14.3 32 32l0 32 48 0c26.5 0 48 21.5 48 48l0 48L0 160l0-48C0 85.5 21.5 64 48 64l48 0 0-32c0-17.7 14.3-32 32-32zM0 192l448 0 0 272c0 26.5-21.5 48-48 48L48 512c-26.5 0-48-21.5-48-48L0 192zm64 80l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zM64 400l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zm112 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16z" />
                        </svg>
                    </button>
                    <label class="form-label input-group-text" style="height: 54px; width:187px"
                        id="fecha_hora"></label>
                    <script>
                        function updateTime() {
                            var now = new Date();
                            var formattedTime = now.getFullYear() + '-' + (now.getMonth() + 1).toString().padStart(2, '0') + '-' + now
                                .getDate().toString().padStart(2, '0') + ' ' + now.getHours().toString().padStart(2, '0') + ':' + now
                                .getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');
                            document.getElementById('fecha_hora').textContent = formattedTime;
                        }
                        setInterval(updateTime, 1000);
                        updateTime();
                    </script>
                </div>
            </div>
        </form>

        <!-- Muestra el estado de conectividad en la esquina superior de la foto de pérfil -->
        <div class="connection-corner-indicator" id="connection-corner">
            <div id="connection-status" class="connection-dot online"></div>
        </div>

        <ul class="navbar-nav flex-nowrap ms-auto">
            @if (Auth::check() && Auth::user()->user_level == 1)
                <li class="nav-item dropdown no-arrow mx-1">
                    <div class="nav-item dropdown no-arrow">
                        <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown"
                            href="#">
                            <span class="badge bg-danger badge-counter">{{ $cupo ?? 0 }}</span>
                            <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512">
                                <path fill="#9b9b9b"
                                    d="M224 0c-17.7 0-32 14.3-32 32V51.2C119 66 64 130.6 64 208v18.8c0 47-17.3 92.4-48.5 127.6l-7.4 8.3c-8.4 9.4-10.4 22.9-5.3 34.4S19.4 416 32 416H416c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-7.4-8.3C401.3 319.2 384 273.9 384 226.8V208c0-77.4-55-142-128-156.8V32c0-17.7-14.3-32-32-32zm45.3 493.3c12-12 18.7-28.3 18.7-45.3H224 160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7z" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-list animated--grow-in">
                            <h6 class="dropdown-header">Doctores sin cupos</h6>
                            @if (isset($doctors))
                                @foreach ($doctors as $doctor)
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="dropdown-list-image me-3">
                                            @if ($doctor->image == null)
                                                <img class="border rounded-circle img-profile"
                                                    src="https://i.postimg.cc/hjSBbZX4/doctor.png">
                                            @else
                                                <img class="border rounded-circle img-profile"
                                                    src="{{ Storage::url('public/perfiles/' . $doctor->image) }}"
                                                    onerror="this.src='https://i.postimg.cc/hjSBbZX4/doctor.png';">
                                            @endif
                                            @switch($doctor->user_status ?? 1)
                                                @case(0)
                                                    <div class="bg-warning status-indicator"></div>
                                                @break

                                                @case(1)
                                                    <div class="bg-success status-indicator"></div>
                                                @break

                                                @default
                                            @endswitch
                                        </div>
                                        <div class="fw-bold">
                                            <div class="text-truncate">
                                                <span>{{ $doctor->specialization_name ?? 'N/A' }}</span>
                                                <span>{{ $doctor->user_name ?? 'N/A' }}</span>
                                            </div>
                                            <p class="small text-gray-500 mb-0">Cupos: {{ $doctor->cupo_doctor ?? 0 }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                            <a class="dropdown-item text-center small text-gray-500" href="#">Ver Todas</a>
                        </div>
                    </div>
                </li>
            @endif

            <div class="d-none d-sm-block topbar-divider"></div>
            <li class="nav-item dropdown no-arrow">
                <div class="nav-item dropdown no-arrow">
                    <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#">
                        <div class="user-info-container">
                            <div class="user-details">
                                <span
                                    class="d-none d-lg-inline me-2 text-gray-600 small user-name">{{ Auth::check() ? Auth::user()->names : 'Invitado' }}</span>
                                <!-- Muestra el estado de conectividad debajo del nombre -->
                                <div class="connection-status-inline" id="connection-inline">
                                    <span class="connection-text-small online" id="connection-text-small">En línea</span>
                                </div>
                            </div>
                            @if (Auth::check())
                                @if (Auth::user()->image == null)
                                    <img class="border rounded-circle img-profile"
                                        src="https://i.postimg.cc/hjSBbZX4/doctor.png">
                                @else
                                    <img class="border rounded-circle img-profile"
                                        src="{{ Storage::url('public/perfiles/' . Auth::user()->image) }}"
                                        onerror="this.src='https://i.postimg.cc/hjSBbZX4/doctor.png';">
                                @endif
                            @else
                                <img class="border rounded-circle img-profile"
                                    src="https://i.postimg.cc/hjSBbZX4/doctor.png">
                            @endif
                        </div>
                    </a>
                    <div class="dropdown-menu shadow dropdown-menu-end animated--grow-in bg-gray">
                        <a class="dropdown-item" href="{{ url('recisa/perfil') }}">
                            <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512">
                                <path fill="#c8c8c8"
                                    d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304l-91.4 0z" />
                            </svg>&nbsp;Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('logout') }}">
                            <svg style="width: 24px; height: 24px;" fill="#ff0000" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 576 512">
                                <path
                                    d="M320 32c0-9.9-4.5-19.2-12.3-25.2S289.8-1.4 280.2 1l-179.9 45C79 51.3 64 70.5 64 92.5L64 448l-32 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0 192 0 32 0 0-32 0-448zM256 256c0 17.7-10.7 32-24 32s-24-14.3-24-32s10.7-32 24-32s24 14.3 24 32zm96-128l96 0 0 352c0 17.7 14.3 32 32 32l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0 0-320c0-35.3-28.7-64-64-64l-96 0 0 64z" />
                            </svg>&nbsp;Salir
                        </a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>


<script>
    let lastConnectionState = navigator.onLine;

    // Función para mostrar notificaciones toast con icono apropiado
    function showNotification(message, type) {
        // Usar iconos FontAwesome apropiados para notificaciones
        const icon = type === 'online'
            ? '<i class="fas fa-wifi"></i>'
            : '<i class="fas fa-wifi-slash"></i>';
        const bgColor = type === 'online' ? '#10B981' : '#EF4444';

        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        `;

        notification.innerHTML = `
            <span style="font-size: 18px;">${icon}</span>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    async function updateConnectionIndicator() {
        // OPCIÓN 1: Indicador de esquina
        const cornerIndicator = document.getElementById('connection-status');
        const inlineText = document.getElementById('connection-text-small');

        // Verificación real de conectividad (no solo navigator.onLine)
        let isOnline = navigator.onLine;

        // Si navigator.onLine dice que está online, hacer ping real
        if (isOnline) {
            // Esperar a que offline-manager esté disponible
            if (window.recisaOffline && window.recisaOffline.checkConnectivity) {
                try {
                    isOnline = await window.recisaOffline.checkConnectivity();
                } catch (e) {
                    console.warn('[Header] Error al verificar conectividad:', e);
                }
            } else {
                // Fallback: verificación simple si offline-manager no está disponible
                try {
                    const controller = new AbortController();
                    setTimeout(() => controller.abort(), 3000);
                    const response = await fetch('/?ping=' + Date.now(), {
                        method: 'HEAD',
                        signal: controller.signal,
                        cache: 'no-cache'
                    });
                    isOnline = response.ok;
                } catch (e) {
                    isOnline = false;
                }
            }
        }

        applyConnectionStyles(isOnline);

        function applyConnectionStyles(online) {
            // Actualizar indicador de esquina
            if (cornerIndicator) {
                cornerIndicator.classList.remove('online', 'offline', 'checking');
                cornerIndicator.classList.add(online ? 'online' : 'offline');
            }

            // Actualizar texto inline
            if (inlineText) {
                inlineText.textContent = online ? 'En línea' : 'Sin conexión';
                inlineText.className = `connection-text-small ${online ? 'online' : 'offline'}`;
            }

            // Mostrar notificación solo cuando cambia el estado
            if (lastConnectionState !== online) {
                const message = online ? 'Conexión restaurada' : 'Conexión perdida';
                const type = online ? 'online' : 'offline';
                showNotification(message, type);
            }

            lastConnectionState = online;
        }
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', () => {
        // Verificar inmediatamente
        updateConnectionIndicator();

        // Eventos de conexión (estos son instantáneos)
        window.addEventListener('online', updateConnectionIndicator);
        window.addEventListener('offline', updateConnectionIndicator);

        // Verificar cada 30 segundos (reducido de 5 para evitar cambios rápidos)
        setInterval(updateConnectionIndicator, 30000);
    });
</script>

<style>
    .connection-corner-indicator {
        position: fixed;
        top: 15px;
        right: 15px;
        z-index: 1040;
        pointer-events: none;
    }

    .connection-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .connection-dot.online {
        background: #10B981;
        animation: pulse-simple 2s infinite;
    }

    .connection-dot.offline {
        background: #EF4444;
        animation: pulse-offline-simple 1.5s infinite;
    }

    @keyframes pulse-simple {
        0% {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15), 0 0 0 8px rgba(16, 185, 129, 0);
        }

        100% {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    @keyframes pulse-offline-simple {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    .user-info-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        align-items: end;
        text-align: right;
    }

    .connection-status-inline {
        display: flex;
        align-items: center;
        margin-top: 2px;
    }

    .connection-text-small {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: color 0.3s ease;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .connection-text-small.online {
        color: #10B981;
        background: rgba(16, 185, 129, 0.1);
    }

    .connection-text-small.offline {
        color: #EF4444;
        background: rgba(239, 68, 68, 0.1);
    }

    /* Notificación de estado */
    .connection-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        font-size: 14px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 1050;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Mantener el estilo existente del perfil */
    .img-profile {
        max-width: 40px;
        max-height: 40px;
    }

    /* Responsivo */
    @media (max-width: 991px) {
        .connection-status-inline {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .connection-corner-indicator {
            top: 10px;
            right: 10px;
        }
    }

    /* Animaciones para notificaciones */
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
</style>
