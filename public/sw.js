// Service worker minimal: hanya agar aplikasi dapat dipasang (PWA).
// Sengaja TIDAK meng-cache halaman, supaya data absensi & sesi login selalu terbaru.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
