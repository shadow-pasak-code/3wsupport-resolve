// ชื่อ cache
const CACHE_NAME = 'sar-management-cache-v1';
const urlsToCache = [
    '/',
    '/images/icon-192x192.png',
    '/images/icon-512x512.png'
];

// ติดตั้ง Service Worker
self.addEventListener('install', (event) => {
    //console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Service Worker: Caching files');
            return cache.addAll(urlsToCache);
        })
    );
});

// เปิดใช้งาน Service Worker
self.addEventListener('activate', (event) => {
    //console.log('Service Worker: Activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        //console.log('Service Worker: Clearing old cache');
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// จัดการ fetch requests
self.addEventListener('fetch', (event) => {
    //console.log('Service Worker: Fetching', event.request.url);
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
