<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RECISA - Sin Conexión</title>
    <link rel="icon" href="{{ asset('assets/img/escudo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #00476D 0%, #00688B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
        }
        .offline-container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
        }
        .offline-icon {
            font-size: 5rem;
            color: #EF4444;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .offline-title {
            color: #00476D;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .offline-subtitle {
            color: #EF4444;
            font-size: 1.2rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .pending-badge {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 1rem;
        }
        .feature-list {
            text-align: left;
            margin: 2rem 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            color: #4B5563;
        }
        .feature-item i {
            color: #10B981;
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        .retry-btn {
            background: #00476D;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .retry-btn:hover {
            background: #00688B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,71,109,0.3);
        }
        .logo-img {
            max-width: 100px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <img src="{{ asset('assets/img/logo.png') }}" alt="RECISA Logo" class="logo-img">

        <div class="offline-icon">
            <i class="fas fa-wifi-slash"></i>
        </div>

        <h1 class="offline-title">RECISA - Gestión de Citas</h1>
        <p class="offline-subtitle">You're offline</p>

        <p style="color: #6B7280;">No tienes conexión a Internet, pero algunas funcionalidades siguen disponibles:</p>

        <div class="feature-list">
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Registrar Paciente (se sincronizará al reconectar)</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Registrar Cita (se sincronizará al reconectar)</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Ver pacientes y citas guardadas</span>
            </div>
        </div>

        <div id="pending-count-container" style="display: none;">
            <div class="pending-badge">
                <i class="fas fa-clock"></i>
                <span id="pending-count">0</span> registros pendientes de sincronizar
            </div>
        </div>

        <button class="retry-btn mt-3" onclick="checkConnection()">
            <i class="fas fa-sync-alt"></i> Reintentar Conexión
        </button>

        <p style="margin-top: 1.5rem; color: #9CA3AF; font-size: 0.9rem;">
            La aplicación se sincronizará automáticamente cuando recuperes la conexión
        </p>
    </div>

    <script>
        // Verificar conexión
        function checkConnection() {
            if (navigator.onLine) {
                window.location.reload();
            } else {
                alert('Aún no hay conexión. Por favor intenta de nuevo más tarde.');
            }
        }

        // Mostrar contador de pendientes si existe IndexedDB
        async function showPendingCount() {
            try {
                const db = await openDb();
                const tx = db.transaction('pending-requests', 'readonly');
                const store = tx.objectStore('pending-requests');
                const count = await store.count();

                if (count > 0) {
                    document.getElementById('pending-count').textContent = count;
                    document.getElementById('pending-count-container').style.display = 'block';
                }
            } catch (error) {
                console.log('No hay solicitudes pendientes');
            }
        }

        function openDb() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('recisa-offline-db', 51);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        }

        // Listener para cuando vuelve la conexión
        window.addEventListener('online', () => {
            window.location.reload();
        });

        // Cargar contador al iniciar
        showPendingCount();
    </script>

    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
