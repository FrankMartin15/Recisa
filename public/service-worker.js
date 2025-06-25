// // public/service-worker.js - VERSIÓN SIN ERRORES DE ASSETS

// const CACHE_VERSION = 'recisa-fixed-v2';
// const CACHE_NAME = `${CACHE_VERSION}`;

// console.log(`[SW] Service Worker ${CACHE_VERSION} iniciado`);

// // Assets esenciales mínimos (solo los que SABEMOS que existen)
// const STATIC_ASSETS = [
//     '/',
//     '/connectivity-check'
// ];

// // INSTALACIÓN SIMPLIFICADA
// self.addEventListener('install', (event) => {
//     console.log(`[SW INSTALL] Instalando ${CACHE_NAME}`);
//     event.waitUntil(
//         caches.open(CACHE_NAME)
//             .then(cache => {
//                 console.log(`[SW INSTALL] Cache ${CACHE_NAME} abierto`);
                
//                 // Intentar cachear assets sin fallar si no existen
//                 return Promise.allSettled(
//                     STATIC_ASSETS.map(url => 
//                         cache.add(url).catch(error => {
//                             console.warn(`[SW INSTALL] Asset no disponible: ${url}`);
//                             return null;
//                         })
//                     )
//                 );
//             })
//             .then(() => {
//                 console.log(`[SW INSTALL] Instalación completada`);
//             })
//     );
    
//     self.skipWaiting();
// });

// // ACTIVACIÓN
// self.addEventListener('activate', (event) => {
//     console.log(`[SW ACTIVATE] Activando ${CACHE_NAME}`);
//     event.waitUntil(
//         caches.keys().then(keys => {
//             return Promise.all(
//                 keys
//                     .filter(key => key.startsWith('recisa-') && key !== CACHE_NAME)
//                     .map(key => {
//                         console.log(`[SW ACTIVATE] Eliminando cache: ${key}`);
//                         return caches.delete(key);
//                     })
//             );
//         }).then(() => {
//             console.log('[SW ACTIVATE] Activación completada');
//             return self.clients.claim();
//         })
//     );
// });

// // MANEJO DE FETCH ULTRA SIMPLIFICADO
// self.addEventListener('fetch', (event) => {
//     const request = event.request;
//     const url = new URL(request.url);

//     // Ignorar peticiones que no son del mismo origen
//     if (url.origin !== self.location.origin) {
//         return;
//     }
//     if (request.method !== 'GET') {
//         // Ignoramos la petición y no hacemos nada. El navegador la manejará normalmente.
//         return;
//     }

//     // MANEJO ESPECIAL PARA CONNECTIVITY CHECK
//     if (url.pathname === '/connectivity-check') {
//         event.respondWith(
//             fetch(request)
//                 .then(response => response)
//                 .catch(() => {
//                     return new Response(JSON.stringify({
//                         status: 'offline',
//                         server_time: new Date().toISOString(),
//                         connection: 'unavailable'
//                     }), {
//                         status: 503,
//                         headers: { 'Content-Type': 'application/json' }
//                     });
//                 })
//         );
//         return;
//     }

//     // MANEJO ESPECIAL PARA UPDATE CONNECTION STATUS
//     if (url.pathname === '/update-connection-status') {
//         event.respondWith(
//             fetch(request)
//                 .then(response => response)
//                 .catch(() => {
//                     return new Response(JSON.stringify({
//                         message: 'Status update queued (offline)'
//                     }), {
//                         status: 200,
//                         headers: { 'Content-Type': 'application/json' }
//                     });
//                 })
//         );
//         return;
//     }

//     // PARA NAVEGACIÓN (documentos HTML)
//     if (request.mode === 'navigate' || 
//         (request.method === 'GET' && request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        
//         event.respondWith(
//             fetch(request)
//                 .then(response => {
//                     // Si la respuesta es buena, cachear para uso futuro
//                     if (response.ok) {
//                         const responseToCache = response.clone();
//                         caches.open(CACHE_NAME).then(cache => {
//                             cache.put(request, responseToCache).catch(() => {
//                                 // Ignorar errores de caché
//                             });
//                         }).catch(() => {
//                             // Ignorar errores de caché
//                         });
//                     }
//                     return response;
//                 })
//                 .catch(() => {
//                     // Error de red - intentar servir desde caché
//                     return caches.match(request).then(cachedResponse => {
//                         if (cachedResponse) {
//                             return cachedResponse;
//                         }
                        
//                         // Página offline
//                         return new Response(`
//                             <!DOCTYPE html>
//                             <html>
//                             <head>
//                                 <title>Sin Conexión - RECISA</title>
//                                 <meta charset="utf-8">
//                                 <meta name="viewport" content="width=device-width, initial-scale=1">
//                                 <style>
//                                     body { 
//                                         font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; 
//                                         text-align: center; 
//                                         padding: 50px; 
//                                         background: #f8f9fa;
//                                         margin: 0;
//                                     }
//                                     .offline-container {
//                                         max-width: 400px;
//                                         margin: 0 auto;
//                                         background: white;
//                                         padding: 40px;
//                                         border-radius: 10px;
//                                         box-shadow: 0 2px 10px rgba(0,0,0,0.1);
//                                     }
//                                     .offline-icon { font-size: 48px; margin-bottom: 20px; }
//                                     .offline-title { color: #333; margin-bottom: 15px; }
//                                     .offline-message { color: #666; margin-bottom: 25px; }
//                                     .retry-btn {
//                                         background: #00476D;
//                                         color: white;
//                                         border: none;
//                                         padding: 12px 24px;
//                                         border-radius: 5px;
//                                         cursor: pointer;
//                                         font-size: 16px;
//                                     }
//                                     .retry-btn:hover { background: #003556; }
//                                 </style>
//                             </head>
//                             <body>
//                                 <div class="offline-container">
//                                     <div class="offline-icon">📱</div>
//                                     <h1 class="offline-title">Modo Offline</h1>
//                                     <p class="offline-message">
//                                         No hay conexión a internet.<br>
//                                         La aplicación funcionará con datos locales.
//                                     </p>
//                                     <button class="retry-btn" onclick="window.location.reload()">
//                                         🔄 Intentar de nuevo
//                                     </button>
//                                 </div>
//                             </body>
//                             </html>
//                         `, {
//                             status: 200,
//                             headers: { 'Content-Type': 'text/html; charset=utf-8' }
//                         });
//                     });
//                 })
//         );
//         return;
//     }

//     // PARA ASSETS (CSS, JS, imágenes) - Strategy: Network First con silent fallback
//     if (request.method === 'GET') {
//         event.respondWith(
//             fetch(request)
//                 .then(response => {
//                     // Si la respuesta es buena, cachear
//                     if (response.ok) {
//                         const responseToCache = response.clone();
//                         caches.open(CACHE_NAME).then(cache => {
//                             cache.put(request, responseToCache).catch(() => {
//                                 // Ignorar errores de caché silenciosamente
//                             });
//                         }).catch(() => {
//                             // Ignorar errores de caché silenciosamente
//                         });
//                     }
//                     return response;
//                 })
//                 .catch(() => {
//                     // Error de red - intentar servir desde caché
//                     return caches.match(request).then(cachedResponse => {
//                         if (cachedResponse) {
//                             return cachedResponse;
//                         }
                        
//                         // ✅ PARA ASSETS NO ENCONTRADOS: NO THROW ERROR
//                         // Simplemente devolver una respuesta vacía apropiada
                        
//                         if (request.url.includes('.js')) {
//                             return new Response('// Asset no disponible offline', {
//                                 status: 200,
//                                 headers: { 'Content-Type': 'application/javascript' }
//                             });
//                         }
                        
//                         if (request.url.includes('.css')) {
//                             return new Response('/* Asset no disponible offline */', {
//                                 status: 200,
//                                 headers: { 'Content-Type': 'text/css' }
//                             });
//                         }
                        
//                         if (request.url.includes('.jpg') || request.url.includes('.png') || request.url.includes('.gif')) {
//                             // Para imágenes, devolver un 404 silencioso
//                             return new Response('', {
//                                 status: 404,
//                                 statusText: 'Not Found'
//                             });
//                         }
                        
//                         // Para otros assets, devolver respuesta vacía
//                         return new Response('', {
//                             status: 200,
//                             headers: { 'Content-Type': 'text/plain' }
//                         });
//                     });
//                 })
//         );
//         return;
//     }

//     // Para métodos que no son GET (POST, PUT, etc.), dejar que pasen directamente
//     // El offline-manager.js manejará estos casos
// });

// // MANEJO DE MENSAJES
// self.addEventListener('message', (event) => {
//     console.log('[SW MESSAGE]', event.data);
    
//     if (event.data && event.data.type === 'SKIP_WAITING') {
//         self.skipWaiting();
//     }
    
//     if (event.data && event.data.type === 'GET_CACHE_STATUS') {
//         caches.keys().then(cacheNames => {
//             event.ports[0].postMessage({
//                 type: 'CACHE_STATUS',
//                 caches: cacheNames,
//                 currentVersion: CACHE_VERSION
//             });
//         });
//     }
// });

// // ✅ MANEJO DE ERRORES SIN THROW
// self.addEventListener('error', (event) => {
//     console.warn('[SW ERROR]', event.error);
//     // No propagar el error
//     event.preventDefault();
// });

// self.addEventListener('unhandledrejection', (event) => {
//     console.warn('[SW UNHANDLED REJECTION]', event.reason);
//     // ✅ PREVENIR que el error se propague y cause problemas
//     event.preventDefault();
// });

// console.log(`[SW] Service Worker ${CACHE_VERSION} listo`);