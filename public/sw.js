// Retired service worker. It has NO fetch handler (so it never intercepts or blanks a
// page) and unregisters itself + clears caches on activation, undoing the earlier version.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        try {
            const keys = await caches.keys();
            await Promise.all(keys.map((k) => caches.delete(k)));
            await self.registration.unregister();
        } catch (e) {}
    })());
});
