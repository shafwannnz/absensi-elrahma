/**
 * Jam real-time WIB.
 * Waktu awal diambil dari server (data-server-ms), lalu dihitung selisihnya
 * terhadap jam perangkat, sehingga tetap akurat walau jam HP/PC salah.
 */
const TIMEZONE = 'Asia/Jakarta';

const clocks = document.querySelectorAll('[data-clock]');

if (clocks.length) {
    // Selisih antara jam server dan jam perangkat (milidetik)
    const offset = Number(clocks[0].dataset.serverMs) - Date.now();

    const dateFormat = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric', timeZone: TIMEZONE,
    });
    const timeFormat = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: TIMEZONE,
    });

    const tick = () => {
        const now = new Date(Date.now() + offset);

        clocks.forEach((el) => {
            // id-ID memakai titik (16.20.35); kita ganti menjadi titik dua
            el.querySelector('[data-clock-time]').textContent = timeFormat.format(now).replace(/\./g, ':');
            el.querySelector('[data-clock-date]').textContent = dateFormat.format(now);
        });

        // Jadwalkan tepat di pergantian detik berikutnya
        setTimeout(tick, 1000 - ((Date.now() + offset) % 1000));
    };

    tick();
}