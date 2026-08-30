const CACHE_NAME = 'ternakapp-cache-v2';
const STATIC_ASSETS = [
    './',
    'home.php',
    'ajax_marketplace.php',
    'style.css',
    'offline.html',
    'manifest.json',
    'images/icons/icon-192.png',
    'images/icons/icon-512.png',
    'images/icons/apple-touch-icon.png',
    'images/icons/icon.svg',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Install Service Worker & Pre-cache Static Assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('Gagal memuat beberapa aset statis ke cache:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Service Worker & Clean Up Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Strategy: Network-First with Offline Fallback for HTML, Stale-While-Revalidate for Assets
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Abaikan permintaan non-GET (misalnya POST login, update, simpan)
    if (request.method !== 'GET') {
        return;
    }

    // Untuk permintaan navigasi halaman HTML
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Fallback ke halaman offline jika tidak ada di cache
                    return caches.match('offline.html');
                })
        );
        return;
    }

    // Untuk berkas aset statis (CSS, JS, Fonts, Images)
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                // Fetch di background untuk update cache (Stale-While-Revalidate)
                fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                    }
                }).catch(() => {/* Offline, gunakan versi cached */});

                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                const responseToCache = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(request, responseToCache);
                });

                return networkResponse;
            });
        })
    );
});
