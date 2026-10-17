import { loadModels, startCamera, stopCamera, captureAveraged } from '../lib/face.js';
import { setStatus } from '../lib/ui.js';

/**
 * Widget pendaftaran wajah. Dipakai di form admin (tambah/ubah mahasiswa)
 * dan halaman "Daftar Wajah" milik mahasiswa.
 */
document.querySelectorAll('[data-face-capture]').forEach((root) => {
    const video = root.querySelector('[data-video]');
    const status = root.querySelector('[data-status]');
    const input = root.querySelector('[data-input]');
    const btnStart = root.querySelector('[data-start]');
    const btnCapture = root.querySelector('[data-capture]');
    let stream = null;

    btnStart.addEventListener('click', async () => {
        btnStart.disabled = true;
        try {
            setStatus(status, 'Memuat model wajah… (pertama kali bisa beberapa detik)');
            await loadModels();
            stream = await startCamera(video);
            video.parentElement.classList.remove('hidden');
            btnCapture.disabled = false;
            setStatus(status, 'Kamera siap. Hadap lurus ke kamera dengan cahaya cukup, lalu tekan "Ambil wajah".');
        } catch (e) {
            btnStart.disabled = false;
            setStatus(status, e.message, 'error');
        }
    });

    btnCapture.addEventListener('click', async () => {
        btnCapture.disabled = true;
        setStatus(status, 'Tahan posisi… sedang mengambil beberapa sampel wajah.');
        try {
            const descriptor = await captureAveraged(video);
            if (!descriptor) {
                setStatus(status, 'Wajah tidak terdeteksi jelas. Dekatkan wajah, tambah cahaya, lalu coba lagi.', 'error');
                btnCapture.disabled = false;
                return;
            }
            input.value = JSON.stringify(descriptor);
            stopCamera(stream);
            video.parentElement.classList.add('hidden');
            setStatus(status, 'Wajah berhasil diambil. Klik tombol Simpan untuk menyimpan.', 'ok');
            btnCapture.textContent = 'Ambil ulang';
            btnCapture.disabled = true;
            btnStart.disabled = false;
            btnStart.textContent = 'Nyalakan kamera lagi';
        } catch (e) {
            setStatus(status, e.message, 'error');
            btnCapture.disabled = false;
        }
    });
});
