const CACHE_NAME = 'recisa-cache-v2';
const API_CACHE_NAME = 'recisa-api-cache-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/assets/img/logo.png',
    '/assets/img/escudo.png',
    '/assets/img/error-404-monochrome.svg',
    '/assets/img/hospital-building.png',
    '/assets/img/PCM-Salud.webp',
    '/assets/img/avatars/avatar1.jpeg',
    '/assets/img/avatars/avatar2.jpeg',
    '/assets/img/avatars/avatar3.jpeg',
    '/assets/img/avatars/avatar4.jpeg',
    '/assets/img/avatars/avatar5.jpeg',
    '/assets/img/avatars/historia-clinica.png',
    '/assets/img/avatars/lista.png',
    '/assets/img/dogs/image2.jpeg',
    '/assets/img/dogs/image3.jpeg',
    '/assets/img/dogs/Red de Salud.png',
    '/assets/bootstrap/css/bootstrap.min.css',
    '/assets/bootstrap/js/bootstrap.min.js',
    '/assets/css/Billing-Table-with-Add-Row--Fixed-Header-Feature.css',
    '/assets/css/Data-Table.css',
    '/assets/css/Data-Table-styles.css',
    '/assets/css/Footer-Basic-icons.css',
    '/assets/css/FORM.css',
    '/assets/css/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.css',
    '/assets/css/Ludens-Users---25-After-Register.css',
    '/assets/css/Pretty-Registration-Form-.css',
    '/assets/css/Register-form.css',
    '/assets/css/Report.css',
    '/assets/css/Responsive-Form-Contact-Form-Clean.css',
    '/assets/css/Responsive-Form.css',
    '/assets/css/Table-With-Search.css',
    '/assets/css/Table-With-Search-search-table.css',
    '/assets/css/menu_bar.css',
    '/assets/css/style_history_patient.css',
    '/assets/css/template.css',
    '/assets/js/Billing-Table-with-Add-Row--Fixed-Header-Feature.js',
    '/assets/js/digitos_numericos.js',
    '/assets/js/history_patient.js',
    '/assets/js/Ludens---1-Index-Table-with-Search--Sort-Filters-v20-1.js',
    '/assets/js/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.js',
    '/assets/js/manejo_carga_imagen.js',
    '/assets/js/menu_bar.js',
    '/assets/js/mostrar_ocultar.js',
    '/assets/js/scripts.js',
    '/assets/js/Table-With-Search.js',
    '/assets/js/theme.js',
    // Offline PWA Scripts
    '/js/offline/OfflineDatabase.js',
    '/js/offline/SyncManager.js',
    '/js/offline/RecisaOfflineApp.js',
    '/js/offline/OfflineDataLoader.js',
    // Offline fallback page
    '/offline.html'
];

// Páginas que se pueden servir offline
const OFFLINE_PAGES = [
    '/admin/dashboard',
    '/secretary/dashboard',
    '/doctor/dashboard',
    '/recisa/patients/list',
    '/recisa/patients/add',
    '/recisa/appoitnment/list',
    '/recisa/appoitnment/add',
    '/recisa/perfil'
];

// Instalar el Service Worker y cachear recursos estáticos
self.addEventListener('install', (event) => {
    console.log('Service Worker v2 instalando...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Cacheando assets estáticos...');
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.error('Error cacheando assets:', err);
                // Continuar aunque falle algún asset
                return Promise.resolve();
            });
        })
    );
    // Forzar activación inmediata
    self.skipWaiting();
});

// Activar el Service Worker y limpiar caches antiguos
self.addEventListener('activate', (event) => {
    console.log('Service Worker v2 activado.');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME && cache !== API_CACHE_NAME) {
                        console.log('Eliminando cache antiguo:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => {
            // Tomar control de todas las páginas inmediatamente
            return self.clients.claim();
        })
    );
});

// Interceptar solicitudes y manejar online/offline
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    const pathname = url.pathname;

    // Ignorar peticiones de Chrome extensions
    if (url.protocol === 'chrome-extension:') {
        return;
    }

    // Estrategia para APIs (/api/*)
    if (pathname.startsWith('/api/')) {
        event.respondWith(handleApiRequest(event.request));
        return;
    }

    // Estrategia Cache First para recursos estáticos conocidos
    if (STATIC_ASSETS.includes(pathname)) {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request).then(fetchResponse => {
                    // Cachear la respuesta
                    return caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, fetchResponse.clone());
                        return fetchResponse;
                    });
                });
            }).catch(() => {
                // Si falla todo, retornar página offline
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // Para páginas HTML (navegación)
    if (event.request.mode === 'navigate' ||
        (event.request.method === 'GET' && event.request.headers.get('accept').includes('text/html'))) {
        event.respondWith(handleNavigationRequest(event.request));
        return;
    }

    // Network First para todo lo demás
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cachear respuestas exitosas
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});

// Manejar peticiones API
async function handleApiRequest(request) {
    try {
        // Intentar red primero
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cachear respuesta exitosa
            const cache = await caches.open(API_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        // Si falla la red, buscar en cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        // Si no hay cache, retornar error JSON
        return new Response(
            JSON.stringify({
                success: false,
                message: 'Sin conexión. Los datos se sincronizarán cuando haya internet.',
                offline: true
            }),
            {
                status: 503,
                headers: { 'Content-Type': 'application/json' }
            }
        );
    }
}

// Manejar peticiones de navegación (páginas HTML)
async function handleNavigationRequest(request) {
    try {
        // Intentar red primero
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cachear la página
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        // Si falla la red, buscar en cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        // Si no hay cache de la página específica, retornar página offline
        return caches.match('/offline.html');
    }
}

// Escuchar mensajes del cliente
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.keys().then(cacheNames => {
            cacheNames.forEach(cacheName => {
                caches.delete(cacheName);
            });
        });
    }

    if (event.data && event.data.type === 'CACHE_PAGE') {
        const url = event.data.url;
        caches.open(CACHE_NAME).then(cache => {
            cache.add(url);
        });
    }
});

// Background Sync para operaciones pendientes
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-pending-operations') {
        event.waitUntil(syncPendingOperations());
    }
});

async function syncPendingOperations() {
    // Esta función será llamada cuando vuelva la conexión
    // La lógica real está en SyncManager.js del lado del cliente
    console.log('Background sync triggered');

    // Notificar a los clientes que intenten sincronizar
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({
            type: 'SYNC_REQUESTED'
        });
    });
}

// Push notifications (para futuras mejoras)
self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();

        const options = {
            body: data.body || 'Nueva notificación de RECISA',
            icon: '/assets/img/logo.png',
            badge: '/assets/img/escudo.png',
            vibrate: [100, 50, 100],
            data: {
                url: data.url || '/'
            }
        };

        event.waitUntil(
            self.registration.showNotification(data.title || 'RECISA', options)
        );
    }
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
