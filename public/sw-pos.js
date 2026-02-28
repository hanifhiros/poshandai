// Handai POS Service Worker — Offline-first caching for static assets
const CACHE_NAME = 'handai-pos-v1';
const STATIC_ASSETS = [
    '/assets/logo.png',
    '/assets/image.png',
    '/css/app.css',
];

// Install: pre-cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Silently fail if some assets can't be cached
                console.log('[SW] Some static assets failed to cache');
            });
        })
    );
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: stale-while-revalidate for GET requests
self.addEventListener('fetch', event => {
    const { request } = event;

    // Only cache GET requests
    if (request.method !== 'GET') return;

    // Skip non-same-origin requests (CDN fonts, icons, etc.)
    const url = new URL(request.url);
    if (url.origin !== location.origin) return;

    // Skip API/POST-like endpoints
    if (url.pathname.startsWith('/cart/') || url.pathname.startsWith('/api/')) return;

    // For build assets (JS, CSS with hashes), cache-first
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // For images/assets, cache-first with network fallback
    if (/\.(png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|eot)$/i.test(url.pathname) || url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(request).then(cached => {
                const fetchPromise = fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => cached);
                return cached || fetchPromise;
            })
        );
        return;
    }

    // For page navigations/HTML: network-first with cache fallback
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                }
                return response;
            }).catch(() => caches.match(request))
        );
        return;
    }
});
