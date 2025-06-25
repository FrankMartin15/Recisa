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
            @if (Auth::user()->user_level == 1)
                <li class="nav-item dropdown no-arrow mx-1">
                    <div class="nav-item dropdown no-arrow">
                        <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown"
                            href="#">
                            <span class="badge bg-danger badge-counter">{{ $cupo ?? 0 }}</span>
                            <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512">
                                <path fill="#9b9b9b"
                                    d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
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
                                    class="d-none d-lg-inline me-2 text-gray-600 small user-name">{{ Auth::user()->names }}</span>
                                <!-- Muestra el estaado de conectividad debajo del nombre -->
                                <div class="connection-status-inline" id="connection-inline">
                                    <div class="connection-dot-small online" id="connection-dot-small"></div>
                                    <span class="connection-text-small" id="connection-text-small">En línea</span>
                                </div>
                            </div>
                            @if (Auth::user()->image == null)
                                <img class="border rounded-circle img-profile"
                                    src="https://i.postimg.cc/hjSBbZX4/doctor.png">
                            @else
                                <img class="border rounded-circle img-profile"
                                    src="{{ Storage::url('public/perfiles/' . Auth::user()->image) }}"
                                    onerror="this.src='https://i.postimg.cc/hjSBbZX4/doctor.png';">
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

    function updateConnectionIndicator() {
        // OPCIÓN 1: Indicador de esquina
        const cornerIndicator = document.getElementById('connection-status');

        // OPCIÓN 2: Indicador inline debajo del nombre
        const inlineDot = document.getElementById('connection-dot-small');
        const inlineText = document.getElementById('connection-text-small');

        const isOnline = navigator.onLine;

        // Actualizar indicador de esquina
        if (cornerIndicator) {
            cornerIndicator.classList.remove('online', 'offline', 'checking');
            cornerIndicator.classList.add(isOnline ? 'online' : 'offline');
        }

        // Actualizar indicador inline
        if (inlineDot && inlineText) {
            inlineDot.classList.remove('online', 'offline', 'checking');
            inlineDot.classList.add(isOnline ? 'online' : 'offline');

            inlineText.textContent = isOnline ? 'En línea' : 'Sin conexión';
            inlineText.className = `connection-text-small ${isOnline ? 'online' : 'offline'}`;
        }

        // Mostrar notificación solo cuando cambia el estado
        if (lastConnectionState !== isOnline) {
            const message = isOnline ? 'Conexión restaurada' : 'Conexión perdida';
            const type = isOnline ? 'online' : 'offline';
            showNotification(message, type);
        }

        lastConnectionState = isOnline;
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', () => {
        updateConnectionIndicator();
        window.addEventListener('online', updateConnectionIndicator);
        window.addEventListener('offline', updateConnectionIndicator);
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
        gap: 5px;
        margin-top: 2px;
    }

    .connection-dot-small {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .connection-dot-small.online {
        background: #10B981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .connection-dot-small.offline {
        background: #EF4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    .connection-text-small {
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: color 0.3s ease;
    }

    .connection-text-small.online {
        color: #10B981;
    }

    .connection-text-small.offline {
        color: #EF4444;
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
</style>
