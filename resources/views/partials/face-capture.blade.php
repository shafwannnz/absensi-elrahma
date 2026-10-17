{{--
    Widget pendaftaran wajah (dikendalikan resources/js/pages/enroll.js).
    Variabel: $hasFace (bool) -> sudah ada data wajah tersimpan?
--}}
<div data-face-capture class="nb-card space-y-4 p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-xl">Data wajah</h3>
        <span class="nb-badge {{ $hasFace ? 'bg-mint' : 'bg-bubble' }}">{{ $hasFace ? 'Sudah terdaftar' : 'Belum terdaftar' }}</span>
    </div>

    <div class="hidden overflow-hidden rounded-md border-[3px] border-black bg-black">
        {{-- scaleX(-1): tampilan seperti cermin agar natural --}}
        <video data-video class="mx-auto aspect-[4/3] w-full max-w-md -scale-x-100 object-cover" playsinline muted></video>
    </div>

    <p data-status class="hidden"></p>
    <input type="hidden" name="face_descriptor" data-input>

    <div class="flex flex-wrap gap-3">
        <button type="button" data-start class="nb-btn bg-sky">{{ $hasFace ? 'Ambil ulang wajah' : 'Nyalakan kamera' }}</button>
        <button type="button" data-capture class="nb-btn bg-mint" disabled>Ambil wajah</button>
    </div>
    <p class="text-sm font-medium text-zinc-600">Tips: wajah lurus ke kamera, cahaya merata, tanpa masker atau kacamata hitam.</p>
</div>
