/** Kelas Tailwind untuk kotak pesan status (dipindai Tailwind dari file JS ini). */
const TONES = {
    info:  'border-[3px] border-black bg-sky/30 rounded-md p-3 font-bold',
    ok:    'border-[3px] border-black bg-mint rounded-md p-3 font-bold',
    error: 'border-[3px] border-black bg-bubble rounded-md p-3 font-bold',
};

export function setStatus(el, text, tone = 'info') {
    if (!el) return;
    el.className = TONES[tone] ?? TONES.info;
    el.textContent = text;
    el.classList.remove('hidden');
}

/** Jarak Haversine (meter) – hanya untuk tampilan; server menghitung ulang sendiri. */
export function haversine(lat1, lng1, lat2, lng2) {
    const rad = (d) => (d * Math.PI) / 180;
    const a = Math.sin(rad(lat2 - lat1) / 2) ** 2
        + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(rad(lng2 - lng1) / 2) ** 2;
    return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

export function geoErrorMessage(err) {
    switch (err.code) {
        case 1: return 'Izin lokasi ditolak. Izinkan akses lokasi pada pengaturan browser.';
        case 2: return 'Lokasi tidak tersedia. Aktifkan GPS perangkat Anda.';
        case 3: return 'Waktu pencarian lokasi habis. Pindah ke tempat terbuka lalu coba lagi.';
        default: return 'Gagal mendapatkan lokasi.';
    }
}
