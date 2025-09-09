// sw.js
const CACHE = 'dienstplan-v7';

// Nur STATICHE Assets (keine HTML/PHP/Navigations):
const ASSETS = [
  '/styles.css',
  '/app.js',
  '/manifest.webmanifest',
  '/assets/icon-192.png',
  '/assets/icon-512.png',
  '/assets/activities/amzflex.png',
  '/assets/activities/package.png',
  '/assets/activities/sunflower.png',
  '/assets/activities/warehouse.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((c) =>
      Promise.all(ASSETS.map((u) => c.add(new Request(u, { cache: 'reload' }))))
    )
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.map((k) => (k !== CACHE ? caches.delete(k) : Promise.resolve())))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // 1) Navigations (HTML, also auch "/") NICHT aus Cache beantworten
  if (req.method === 'GET' && req.mode === 'navigate') {
    event.respondWith(fetch(req)); // Browser darf Redirects folgen
    return;
  }

  // 2) Für alles andere: cache-first für unsere Assets
  if (req.method === 'GET') {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req).then((resp) => {
          // Nur erfolgreiche, nicht-redirectete Antworten cachen
          if (resp.ok && !resp.redirected && resp.type !== 'opaqueredirect') {
            const copy = resp.clone();
            caches.open(CACHE).then((c) => c.put(req, copy)).catch(() => {});
          }
          return resp;
        });
      })
    );
  }
});
