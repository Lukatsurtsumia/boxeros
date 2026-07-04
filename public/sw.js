// BoxerOS service worker — minimal. Its job is to make the app installable
// (Add to Home Screen / Install). It stays network-first so pages are never stale.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return; // never touch Livewire POSTs etc.
    event.respondWith(fetch(event.request).catch(() => new Response('', { status: 504 })));
});
