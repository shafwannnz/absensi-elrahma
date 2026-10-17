import { loadModels, startCamera, stopCamera, hasFace, detectDescriptor } from '../lib/face.js';
import { setStatus, haversine, geoErrorMessage } from '../lib/ui.js';

const app = document.getElementById('attendance-app');
if (app) init(app);

/** Ambil gambar dari video sebagai JPEG (data URL), diperkecil agar upload ringan. */
function captureSnapshot(video, width = 480) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = Math.round(width * (video.videoHeight / video.videoWidth));
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL('image/jpeg', 0.7);
}

function init(app) {
    const campus = {
        lat: parseFloat(app.dataset.lat),
        lng: parseFloat(app.dataset.lng),
        radius: parseInt(app.dataset.radius, 10),
    };
    const $ = (id) => document.getElementById(id);
    const video = $('video');
    const btnStart = $('btn-start');
    const btnCheckin = $('btn-checkin');
    const faceState = $('face-state');
    const distanceText = $('distance-text');
    const distanceBar = $('distance-bar');
    const gpsState = $('gps-state');
    const message = $('message');

    const state = { position: null, distance: null, faceOk: false, stream: null, running: false, submitting: false, watchId: null };

    // ---------- Peta (Leaflet) ----------
    const L = window.L;
    let map = null;
    let userMarker = null;
    let fitted = false;
    if (L) {
        map = L.map('map').setView([campus.lat, campus.lng], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);
        L.marker([campus.lat, campus.lng]).addTo(map).bindPopup('Kampus');
        L.circle([campus.lat, campus.lng], {
            radius: campus.radius, color: '#000', weight: 3, fillColor: '#ffdd00', fillOpacity: 0.25,
        }).addTo(map);
    }

    // ---------- Lokasi (GPS) ----------
    function onPosition(pos) {
        state.position = pos;
        const { latitude, longitude, accuracy } = pos.coords;
        state.distance = haversine(latitude, longitude, campus.lat, campus.lng);

        const inside = state.distance <= campus.radius;
        distanceText.textContent = `${Math.round(state.distance)} m dari kampus`;
        gpsState.textContent = inside
            ? `Di dalam radius ${campus.radius} m (akurasi GPS ±${Math.round(accuracy)} m)`
            : `Di luar radius ${campus.radius} m. Dekati kampus untuk absen.`;
        distanceBar.style.width = Math.min((state.distance / (campus.radius * 2)) * 100, 100) + '%';
        distanceBar.className = 'h-full ' + (inside ? 'bg-mint' : 'bg-bubble');

        if (map) {
            if (!userMarker) {
                userMarker = L.circleMarker([latitude, longitude], {
                    radius: 9, color: '#000', weight: 3, fillColor: '#4c8dff', fillOpacity: 1,
                }).addTo(map).bindPopup('Posisi Anda');
            } else {
                userMarker.setLatLng([latitude, longitude]);
            }
            if (!fitted) {
                map.fitBounds(L.latLngBounds([[campus.lat, campus.lng], [latitude, longitude]]).pad(0.4));
                fitted = true;
            }
        }
        refreshButton();
    }

    function onGeoError(err) {
        state.position = null;
        state.distance = null;
        gpsState.textContent = geoErrorMessage(err);
        refreshButton();
    }

    if (navigator.geolocation) {
        state.watchId = navigator.geolocation.watchPosition(onPosition, onGeoError, {
            enableHighAccuracy: true, maximumAge: 5000, timeout: 20000,
        });
    } else {
        gpsState.textContent = 'Browser ini tidak mendukung GPS.';
    }

    // ---------- Kamera & deteksi wajah ----------
    btnStart?.addEventListener('click', async () => {
        btnStart.disabled = true;
        try {
            setStatus(message, 'Memuat model wajah… (pertama kali bisa beberapa detik)');
            await loadModels();
            state.stream = await startCamera(video);
            state.running = true;
            $('video-wrap').classList.remove('hidden');
            btnStart.classList.add('hidden');
            message.classList.add('hidden');
            detectLoop();
        } catch (e) {
            btnStart.disabled = false;
            setStatus(message, e.message, 'error');
        }
    });

    async function detectLoop() {
        while (state.running) {
            if (!state.submitting) {
                try {
                    state.faceOk = await hasFace(video);
                    faceState.textContent = state.faceOk
                        ? 'Wajah terdeteksi'
                        : 'Wajah belum terdeteksi. Hadapkan wajah ke kamera.';
                } catch {
                    state.faceOk = false;
                }
                refreshButton();
            }
            await new Promise((r) => setTimeout(r, 500));
        }
    }

    // Tombol absen hanya aktif jika kamera hidup, wajah terlihat, dan lokasi di dalam radius.
    function refreshButton() {
        const inside = state.distance !== null && state.distance <= campus.radius;
        btnCheckin.disabled = !(state.running && state.faceOk && inside && !state.submitting);
    }

    // ---------- Kirim absensi ----------
    btnCheckin.addEventListener('click', async () => {
        state.submitting = true;
        refreshButton();
        setStatus(message, 'Memverifikasi wajah dan lokasi…');

        try {
            const face = await detectDescriptor(video);
            if (!face) throw new Error('Wajah tidak terdeteksi. Coba lagi.');

            const photo = captureSnapshot(video); // selfie saat verifikasi
            const { latitude, longitude, accuracy } = state.position.coords;
            const res = await fetch(app.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ latitude, longitude, accuracy, descriptor: face.descriptor, photo }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Absensi gagal. Coba lagi.');

            onSuccess(data);
        } catch (e) {
            setStatus(message, e.message, 'error');
        } finally {
            state.submitting = false;
            refreshButton();
        }
    });

            function onSuccess(data) {
        state.running = false;
        stopCamera(state.stream);
        if (state.watchId !== null) navigator.geolocation.clearWatch(state.watchId);

        setStatus(message, `${data.message} Pukul ${data.time} WIB.`, 'ok');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Muat ulang agar ringkasan & mode halaman (masuk -> pulang -> selesai) digambar oleh server.
        setTimeout(() => window.location.reload(), 1500);
    }
}
