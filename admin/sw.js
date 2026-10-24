/* Larr Admin PWA service worker */
const BUILD = '1792878544'; /* sello estampado en cada deploy */
const VER = 'larradmin-' + BUILD;
const STATIC = VER + '-static';
const PRECACHE = [
  './offline.html',
  './manifest.webmanifest',
  './icons/icon-192.png',
  './icons/icon-512.png',
  './icons/apple-touch-icon.png'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(STATIC).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('message', e => {
  if (e.data && e.data.type === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k.indexOf('larradmin-') === 0 && k !== STATIC).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

function isStaticAsset(url) {
  return /\.(png|jpg|jpeg|webp|svg|ico|css|js|woff2?|ttf)$/i.test(url.pathname) ||
         url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com';
}

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;                 // never cache writes (POST login, saves)
  const url = new URL(req.url);

  // Static assets: cache-first, refresh in background
  if (isStaticAsset(url)) {
    e.respondWith(
      caches.match(req).then(hit => {
        const net = fetch(req).then(res => {
          if (res && res.status === 200) { const copy = res.clone(); caches.open(STATIC).then(c => c.put(req, copy)); }
          return res;
        }).catch(() => hit);
        return hit || net;
      })
    );
    return;
  }

  // Navigations & dynamic (PHP): network-first, fall back to cache, then offline page
  if (req.mode === 'navigate' || url.pathname.endsWith('.php') || url.pathname.endsWith('/')) {
    e.respondWith(
      fetch(req).then(res => {
        if (res && res.status === 200 && url.origin === location.origin) {
          const copy = res.clone(); caches.open(STATIC).then(c => c.put(req, copy));
        }
        return res;
      }).catch(() => caches.match(req).then(hit => hit || caches.match('./offline.html')))
    );
  }
});
