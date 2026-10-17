/**
 * Pembungkus face-api (window.faceapi, dimuat dari CDN lewat tag <script> di layout).
 * Pengenalan wajah dijalankan di browser; server hanya menerima vektor 128 angka.
 */
const modelsUrl = () => document.querySelector('meta[name="face-models-url"]')?.content;

let modelsPromise = null;

/** Muat model sekali saja (tiny detector + landmark + recognition). */
export function loadModels() {
    const faceapi = window.faceapi;
    if (!faceapi) {
        return Promise.reject(new Error('Library pengenal wajah gagal dimuat. Periksa koneksi internet lalu muat ulang.'));
    }
    if (!modelsPromise) {
        const url = modelsUrl();
        modelsPromise = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(url),
            faceapi.nets.faceLandmark68Net.loadFromUri(url),
            faceapi.nets.faceRecognitionNet.loadFromUri(url),
        ]).catch((e) => {
            modelsPromise = null;
            throw new Error('Model wajah gagal dimuat. Periksa koneksi internet.');
        });
    }
    return modelsPromise;
}

/** Nyalakan kamera depan. Butuh HTTPS (atau localhost). */
export async function startCamera(video) {
    if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Kamera tidak tersedia. Buka lewat HTTPS (atau localhost) pada browser terbaru.');
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false,
        });
        video.srcObject = stream;
        await video.play();
        return stream;
    } catch (e) {
        const denied = e.name === 'NotAllowedError' || e.name === 'SecurityError';
        throw new Error(denied
            ? 'Izin kamera ditolak. Izinkan akses kamera pada pengaturan browser.'
            : 'Kamera tidak dapat dinyalakan: ' + e.message);
    }
}

export function stopCamera(stream) {
    stream?.getTracks().forEach((track) => track.stop());
}

const detectorOptions = () => new window.faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

/** Cek cepat apakah ada wajah (tanpa menghitung descriptor). */
export async function hasFace(video) {
    return !!(await window.faceapi.detectSingleFace(video, detectorOptions()));
}

/** Deteksi satu wajah dan kembalikan descriptor 128 angka (atau null). */
export async function detectDescriptor(video) {
    const result = await window.faceapi
        .detectSingleFace(video, detectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    return result ? { descriptor: Array.from(result.descriptor), score: result.detection.score } : null;
}

/**
 * Ambil beberapa sampel lalu rata-ratakan agar data pendaftaran lebih stabil.
 * Minimal 2 sampel berhasil.
 */
export async function captureAveraged(video, samples = 4, delayMs = 350) {
    const taken = [];
    for (let i = 0; i < samples; i++) {
        const r = await detectDescriptor(video);
        if (r) taken.push(r.descriptor);
        await new Promise((resolve) => setTimeout(resolve, delayMs));
    }
    if (taken.length < 2) return null;

    return taken[0].map((_, idx) => taken.reduce((sum, d) => sum + d[idx], 0) / taken.length);
}
