const CACHE_NAME = 'autocarteras-v1';
// No cacheamos '/' porque es una redirección. Cacheamos el login directamente.
const STATIC_ASSETS = [
    '/login',
    '/dashboard',
    '/manifest.json',
    // Añade aquí tus archivos CSS/JS principales de Vite
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Usamos una técnica más robusta para que un error en un archivo 
            // no rompa toda la instalación del SW
            return Promise.allSettled(
                STATIC_ASSETS.map(url => cache.add(url))
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    // Solo procesamos peticiones GET y de nuestro propio dominio
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) return;

    // Evitamos cachear rutas de la API o procesos sensibles
    if (event.request.url.includes('/api/') || event.request.url.includes('/logout')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) return cachedResponse;

            return fetch(event.request).then(response => {
                // Solo cacheamos si la respuesta es válida y no es una redirección (tipo 0 o 200)
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            }).catch(() => {
                // Opcional: Retornar una página de 'Offline' si nada funciona
            });
        })
    );
});
