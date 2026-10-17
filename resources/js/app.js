import './bootstrap';
import './lib/clock';

// PWA: agar web bisa dipasang di layar utama Android (Chrome > "Instal aplikasi").
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
