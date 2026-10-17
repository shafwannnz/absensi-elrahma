import { setStatus } from '../lib/ui.js';

/**
 * Peta pengaturan lokasi kampus (admin).
 * Klik peta / seret pin untuk memindahkan titik; lingkaran mengikuti nilai radius.
 */
const mapEl = document.getElementById('map');
const L = window.L;

if (mapEl && L) {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius_m');
    const status = document.getElementById('map-status');

    const start = [parseFloat(latInput.value), parseFloat(lngInput.value)];
    const map = L.map(mapEl).setView(start, 16);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const marker = L.marker(start, { draggable: true }).addTo(map);
    const circle = L.circle(start, {
        radius: parseInt(radiusInput.value, 10) || 500,
        color: '#000', weight: 3, fillColor: '#ffdd00', fillOpacity: 0.3,
    }).addTo(map);

    function moveTo(latlng, recenter = false) {
        marker.setLatLng(latlng);
        circle.setLatLng(latlng);
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        if (recenter) map.setView(latlng, 16);
    }

    marker.on('dragend', () => moveTo(marker.getLatLng()));
    map.on('click', (e) => moveTo(e.latlng));

    radiusInput.addEventListener('input', () => {
        const r = parseInt(radiusInput.value, 10);
        if (r > 0) circle.setRadius(r);
    });

    // Ketik manual koordinat -> pin ikut pindah.
    [latInput, lngInput].forEach((el) => el.addEventListener('change', () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
        }
    }));

    // Tombol "Gunakan lokasi saya".
    document.getElementById('btn-locate')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            setStatus(status, 'Browser tidak mendukung GPS.', 'error');
            return;
        }
        setStatus(status, 'Mencari lokasi Anda…');
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                moveTo(L.latLng(pos.coords.latitude, pos.coords.longitude), true);
                setStatus(status, 'Pin dipindah ke lokasi Anda. Klik Simpan untuk menerapkan.', 'ok');
            },
            () => setStatus(status, 'Gagal mengambil lokasi. Izinkan akses lokasi.', 'error'),
            { enableHighAccuracy: true, timeout: 15000 },
        );
    });

    // Peta dibuat saat container sudah tampil; pastikan ukuran tile benar.
    setTimeout(() => map.invalidateSize(), 200);
}
