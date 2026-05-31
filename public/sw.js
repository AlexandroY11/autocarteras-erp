const CACHE_NAME = 'autocarteras-v2';

self.addEventListener('install', event => {
    self.skipWaiting();

    event.waitUntil(
        caches.open(CACHE_NAME).then(cache =>
            cache.addAll(['/manifest.json'])
        )
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );

    self.clients.claim();
});

self.addEventListener('fetch', event => {

    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    if (!request.url.startsWith(self.location.origin)) {
        return;
    }

    const url = new URL(request.url);

    const isStaticAsset =
        /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|webp|avif)$/
        .test(url.pathname);

    const isManifest =
        url.pathname === '/manifest.json';

    if (!isStaticAsset && !isManifest) {
        return;
    }

    event.respondWith(
        caches.match(request).then(cached => {

            if (cached) {
                return cached;
            }

            return fetch(request).then(response => {

                if (response.ok) {
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, response.clone());
                    });
                }

                return response;
            });

        })
    );
});