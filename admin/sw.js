/* Larr Admin PWA service worker */
const BUILD = '1792880080'; /* sello estampado en cada deploy */
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

/* ===== Notificaciones push ===== */
function idbGet(key){
  return new Promise(res => { try{
    const o = indexedDB.open('larradmin', 1);
    o.onupgradeneeded = () => { try{ o.result.createObjectStore('kv'); }catch(e){} };
    o.onsuccess = () => { try{ const tx = o.result.transaction('kv','readonly'); const rq = tx.objectStore('kv').get(key); rq.onsuccess = () => res(rq.result); rq.onerror = () => res(null); }catch(e){ res(null); } };
    o.onerror = () => res(null);
  }catch(e){ res(null); } });
}
self.addEventListener('push', e => {
  e.waitUntil((async () => {
    let title = '🛒 Nueva compra', body = 'Tenés una novedad en la tienda', url = 'orders.php', tag = 'larr-order';
    try {
      const tok = await idbGet('pushToken');
      if (tok) {
        const r = await fetch('./push_latest.php?t=' + encodeURIComponent(tok), { cache: 'no-store' });
        if (r.ok) { const d = await r.json(); if (d.title) title = d.title; if (d.body) body = d.body; if (d.url) url = d.url; if (d.tag) tag = d.tag; }
      }
    } catch (err) {}
    await self.registration.showNotification(title, {
      body, tag, renotify: true, icon: './icons/icon-192.png', badge: './icons/favicon-32.png',
      data: { url }, vibrate: [90, 40, 90]
    });
  })());
});
self.addEventListener('notificationclick', e => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || 'index.php';
  e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(cl => {
    for (const c of cl) { if (c.url.indexOf('/admin/') >= 0 && 'focus' in c) { try { c.navigate(url); } catch (e) {} return c.focus(); } }
    return self.clients.openWindow(url);
  }));
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
