const CACHE_NAME = 'omegaup-pwa-v1';
const OFFLINE_URL = '/offline.html';

const urlsToCache = [
  '/',
  '/offline.html',
  '/css/dist/omegaup_styles.css',
  '/media/omegaup_curves.png',
  '/third_party/bootstrap-4.5.0/css/bootstrap.min.css',
  '/third_party/bootstrap-4.5.0/js/bootstrap.bundle.min.js',
];

// Install event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)),
  );
  self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

// Fetch event
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) return cachedResponse;
      return fetch(event.request).catch(() => caches.match(OFFLINE_URL));
    }),
  );
});