// BoxerOS service worker. Caches static assets (build files, icons, fonts) for instant
// repeat loads, and NEVER intercepts page navigations — so it can't slow or blank a page.
const ASSETS = 'boxeros-assets-v2';

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== ASSETS).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const isAsset = url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icon')
        || url.pathname.endsWith('.svg')
        || url.pathname.endsWith('.png')
        || url.hostname.includes('fonts.gstatic.com');

    // Only cache static assets. Page navigations pass straight to the browser (fast, safe).
    if (!isAsset) return;

    event.respondWith(
        caches.open(ASSETS).then((cache) =>
            cache.match(req).then((hit) =>
                hit || fetch(req).then((res) => {
                    if (res.ok) cache.put(req, res.clone());
                    return res;
                })
            )
        )
    );
});
