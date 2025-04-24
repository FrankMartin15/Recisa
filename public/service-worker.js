const CACHE_NAME = 'recisa-cache-v1';
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
    '/assets/css/all.min.css',
    '/assets/css/Billing-Table-with-Add-Row--Fixed-Header-Feature.css',
    '/assets/css/bootstrap.min.css',
    '/assets/css/bootstrap-select.min.css',
    '/assets/css/Data-Table.css',
    '/assets/css/dataTables.bootstrap5.css',
    '/assets/css/dataTables.bootstrap.min.css',
    '/assets/css/Data-Table-styles.css',
    '/assets/css/Footer-Basic-icons.css',
    '/assets/css/FORM.css',
    '/assets/css/google-fonts-nunito.css',
    '/assets/css/google-fonts-poppins.css',
    '/assets/css/google-fonts-roboto.css',
    '/assets/css/jquery-ui.min.css',
    '/assets/css/lineicons.css',
    '/assets/css/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.css',
    '/assets/css/Ludens-Users---25-After-Register.css',
    '/assets/css/menu_bar.css',
    '/assets/css/Pretty-Registration-Form-.css',
    '/assets/css/Register-form.css',
    '/assets/css/Report.css',
    '/assets/css/Responsive-Form-Contact-Form-Clean.css',
    '/assets/css/Responsive-Form.css',
    '/assets/css/style_history_patient.css',
    '/assets/css/Table-With-Search.css',
    '/assets/css/Table-With-Search-search-table.css',
    '/assets/css/template.css',
    '/assets/css/theme.bootstrap_4.min.css',
    '/assets/js/Billing-Table-with-Add-Row--Fixed-Header-Feature.js',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/bootstrap-select.min.js',
    '/assets/js/dataTables.bootstrap5.js',
    '/assets/js/dataTables.bootstrap.min.js',
    '/assets/js/dataTables.js',
    '/assets/js/digitos_numericos.js',
    '/assets/js/fontawesome-kit.js',
    '/assets/js/history_patient.js',
    '/assets/js/jquery-3.5.1.min.js',
    '/assets/js/jquery-3.7.1.js',
    '/assets/js/jquery.dataTables.min.js',
    '/assets/js/jquery.min.js',
    '/assets/js/jquery.tablesorter.js',
    '/assets/js/jquery-ui.min.js',
    '/assets/js/Ludens---1-Index-Table-with-Search--Sort-Filters-v20-1.js',
    '/assets/js/Ludens---1-Index-Table-with-Search--Sort-Filters-v20.js',
    '/assets/js/manejo_carga_imagen.js',
    '/assets/js/menu_bar.js',
    '/assets/js/mostrar_ocultar.js',
    '/assets/js/scripts.js',
    '/assets/js/sweetalert2@11.js',
    '/assets/js/Table-With-Search.js',
    '/assets/js/theme.js',
    '/assets/js/widget-filter.min.js',
    '/assets/js/widget-storage.min.js'
];

// Instalar el Service Worker y cachear recursos estáticos
self.addEventListener('install', (event) => {
    console.log('Service Worker instalado.');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

// Activar el Service Worker y limpiar caches antiguos
self.addEventListener('activate', (event) => {
    console.log('Service Worker activado.');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('Eliminando cache antiguo:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// Interceptar solicitudes y manejar online/offline
self.addEventListener('fetch', (event) => {
    if (event.request.method === 'POST') {
        // Enviar solicitudes POST directamente al servidor
        event.respondWith(
            fetch(event.request).catch((error) => {
                console.error('Error al manejar la solicitud POST:', error);
                return new Response('No se pudo completar la solicitud POST.', {
                    status: 503,
                    statusText: 'Service Unavailable',
                });
            })
        );
        return;
    }

    if (event.request.method !== 'GET') {
        // Ignorar solicitudes que no sean GET
        return;
    }

    const requestURL = new URL(event.request.url);

    if (STATIC_ASSETS.includes(requestURL.pathname)) {
        // Estrategia Cache First para recursos estáticos
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request).then((response) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, response.clone());
                        return response;
                    });
                });
            })
        );
    } else {
        // Estrategia Network First para solicitudes dinámicas
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        if (response.ok) {
                            cache.put(event.request, response.clone());
                        }
                        return response;
                    });
                })
                .catch(() => {
                    return caches.match(event.request).then((cachedResponse) => {
                        return cachedResponse || new Response('Contenido no disponible offline.', {
                            status: 503,
                            statusText: 'Service Unavailable',
                        });
                    });
                })
        );
    }
});