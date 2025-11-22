const CACHE_VERSION = 'recisa-v5-no-offline-redirect';
const CACHE_STATIC = 'recisa-static-v5';
const CACHE_DYNAMIC = 'recisa-dynamic-v5';
const CACHE_API = 'recisa-api-v5';

// Assets that are absolutely required for the app shell
const STATIC_ASSETS = [
    '/',
    '/assets/img/logo.png',
    '/assets/img/escudo.png',
    '/manifest.json',
    '/assets/js/offline-manager.js',
    // Critical CSS
    '/assets/bootstrap/css/bootstrap.min.css',
    '/assets/css/google-fonts-nunito.css',
    '/assets/css/google-fonts-roboto.css',
    '/assets/fonts/fontawesome-all.min.css',
    '/assets/fonts/font-awesome.min.css',
    '/assets/css/dataTables.bootstrap5.css',
    '/assets/css/dataTables.bootstrap.min.css',
    '/assets/css/menu_bar.css',
    '/assets/css/FORM.css',
    // Critical JS
    '/assets/js/jquery-3.7.1.js',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/dataTables.js',
    '/assets/js/dataTables.bootstrap5.js',
    '/assets/js/jquery.dataTables.min.js',
    '/assets/js/dataTables.bootstrap.min.js',
    '/assets/js/menu_bar.js'
];

// Install Event: Cache Static Assets
self.addEventListener('install', (event) => {
    console.log(`[SW] Installing Service Worker ${CACHE_VERSION}`);
    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(cache => {
                console.log('[SW] Caching App Shell');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate Event: Clean up old caches
self.addEventListener('activate', (event) => {
    console.log(`[SW] Activating Service Worker ${CACHE_VERSION}`);
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_STATIC && key !== CACHE_DYNAMIC && key !== CACHE_API) {
                        console.log('[SW] Removing old cache', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Helper: Network First (for API and HTML navigation)
// Tries network, if fails, tries cache. If both fail, returns offline page (for nav) or error.
const networkFirst = async (request, cacheName) => {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        // NO redirigir a /offline - rompe la funcionalidad offline de la app
        // La app funciona en modo SPA, si ya están en una página, déjala funcionar
        throw error;
    }
};

// Helper: Stale While Revalidate (for Assets: JS, CSS, Images)
// Returns cache immediately, then updates cache from network in background.
const staleWhileRevalidate = async (request, cacheName) => {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);

    const fetchPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(() => {
        // Network failed, nothing to do if we have cache
    });

    return cachedResponse || fetchPromise;
};

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignore non-GET requests (POST/PUT/DELETE handled by offline-manager.js)
    if (request.method !== 'GET') return;

    // Ignore different origin
    if (url.origin !== self.location.origin) return;

    // 1. API Requests -> Network First (Cache API responses for offline viewing)
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/dashboard') || url.pathname === '/') {
        event.respondWith(networkFirst(request, CACHE_API));
        return;
    }

    // 2. Static Assets (JS, CSS, Images, Fonts) -> Stale While Revalidate
    if (request.destination === 'script' ||
        request.destination === 'style' ||
        request.destination === 'image' ||
        request.destination === 'font') {
        event.respondWith(staleWhileRevalidate(request, CACHE_STATIC));
        return;
    }

    // 3. Default -> Network First
    event.respondWith(networkFirst(request, CACHE_DYNAMIC));
});