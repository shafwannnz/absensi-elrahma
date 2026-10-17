@extends('layouts.app')
@section('title', 'Absen')
@section('heading', 'Halo, '.explode(' ', auth()->user()->name)[0])
@section('subheading', now()->translatedFormat('l, d F Y').' · Absen dibuka '.$setting->start_time.'–'.$setting->end_time.' WIB, tepat waktu sampai '.$setting->on_time_until.'.')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
@endpush

@section('content')
{{-- Hasil absensi (diisi server jika sudah absen, atau oleh JS setelah berhasil) --}}
<div id="result-box" class="nb-card {{ $today ? ($today->status === 'terlambat' ? 'bg-tangerine' : 'bg-mint') : 'hidden' }} mb-6 p-6 sm:p-8">
    <p class="font-bold">Absensi hari ini tercatat</p>
    <p id="result-label" class="font-display text-5xl font-extrabold sm:text-6xl">{{ $today?->status_label }}</p>
    <p id="result-time" class="mt-2 font-bold">
        @if ($today){{ $today->checked_in_at->format('H:i') }} WIB · {{ $today->distance_m }} m dari kampus @endif
    </p>
    <a href="{{ route('student.history') }}" class="nb-btn mt-4 bg-white">Lihat riwayat</a>
</div>

@if (! $today)
    @if (! $hasFace)
        <div class="nb-card bg-bubble p-6">
            <h2 class="text-2xl">Daftarkan wajah dulu</h2>
            <p class="mt-1 max-w-prose font-medium">Wajah Anda belum terdaftar, jadi absensi belum bisa dilakukan. Pendaftaran hanya sekali dan butuh kamera depan.</p>
            <a href="{{ route('student.face.edit') }}" class="nb-btn mt-4 bg-white">Daftarkan wajah</a>
        </div>
    @else
        <div id="attendance-app" class="grid gap-6 lg:grid-cols-2"
             data-lat="{{ $setting->latitude }}" data-lng="{{ $setting->longitude }}"
             data-radius="{{ $setting->radius_m }}" data-url="{{ route('student.attendance.store') }}">

            {{-- Kamera --}}
            <section id="capture-panel" class="nb-card space-y-4 p-4 sm:p-5">
                <h2 class="text-xl">1. Verifikasi wajah</h2>

                <div id="video-wrap" class="hidden overflow-hidden rounded-md border-[3px] border-black bg-black">
                    <video id="video" class="aspect-[4/3] w-full -scale-x-100 object-cover" playsinline muted></video>
                </div>
                <p id="face-state" class="font-bold">Kamera belum menyala.</p>
                <p id="message" class="hidden"></p>

                <button id="btn-start" type="button" class="nb-btn w-full bg-sky">Nyalakan kamera</button>
                <button id="btn-checkin" type="button" class="nb-btn w-full bg-mint py-3 text-lg" disabled>Absen sekarang</button>
                <p class="text-sm font-medium text-zinc-600">Tombol aktif jika wajah terdeteksi dan Anda berada dalam radius {{ $setting->radius_m }} m dari kampus.</p>
            </section>

            {{-- Lokasi --}}
            <section class="nb-card space-y-4 p-4 sm:p-5">
                <h2 class="text-xl">2. Lokasi Anda</h2>
                <div>
                    <p id="distance-text" class="font-display text-3xl font-extrabold">Mencari lokasi…</p>
                    <div class="mt-2 h-5 overflow-hidden rounded-md border-[3px] border-black bg-white">
                        <div id="distance-bar" class="h-full w-0 bg-sky"></div>
                    </div>
                    <p id="gps-state" class="mt-2 font-bold">Izinkan akses lokasi saat diminta browser.</p>
                </div>
                <div id="map" class="z-0 h-64 rounded-md border-[3px] border-black"></div>
            </section>
        </div>
    @endif
@endif
@endsection

@if (! $today && $hasFace)
    @push('scripts')
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
        <script defer src="{{ config('absensi.face.script_url') }}"></script>
        @vite('resources/js/pages/attendance.js')
    @endpush
@endif
