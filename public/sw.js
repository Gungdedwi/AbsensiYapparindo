const CACHE_NAME = 'ay-app-shell-v2';
const APP_SHELL_ASSETS = [
    '/login',
    '/offline',
    '/manifest.json',
    '/icons/icon-192.svg',
    '/icons/icon-512.svg'
];

// Install: Simpan app shell aset minimum ke Cache Storage (tanpa '/' karena redirect)
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(APP_SHELL_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate: Bersihkan cache versi lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Strategy: Network-First dengan Fallback ke Cache App Shell / Offline Page
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Abaikan permintaan selain GET (POST, PUT, DELETE selalu langsung ke jaringan)
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Jangan cache endpoint privat logout atau sync API
    if (url.pathname === '/logout' || url.pathname.includes('/api/')) {
        return;
    }

    // Tangani permintaan navigasi halaman (HTML)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(request).then((cachedResponse) => {
                    return cachedResponse || caches.match('/offline');
                });
            })
        );
        return;
    }

    // Tangani aset statis (CSS, JS, Font, Gambar)
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache di background (Stale-While-Revalidate)
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache));
                    }
                    return networkResponse;
                });
            })
        );
    }
});
