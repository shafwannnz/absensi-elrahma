@extends('layouts.app')
@section('title', 'Absen')
@section('heading', 'Halo, '.explode(' ', auth()->user()->name)[0])
@section('subheading', 'Masuk '.$setting->start_time.'–'.$setting->end_time.' WIB (tepat waktu s.d. '.$setting->on_time_until.'). Pulang '.$setting->checkout_start.'–'.$setting->checkout_end.' WIB.')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
@endpush

@section('content')
{{-- Ringkasan hari ini: absen masuk & absen pulang --}}
@if ($today)
    <div class="mb-6 grid gap-4 md:grid-cols-2">
        <div class="nb-card {{ $today->status === 'terlambat' ? 'bg-tangerine' : 'bg-mint' }} p-6">
            <p class="font-bold">Absen masuk</p>
            <p class="font-display text-5xl font-extrabold">{{ $today->status_label }}</p>
            <p class="mt-2 font-bold">{{ $today->checked_in_at->format('H:i') }} WIB · {{ $today->distance_m }} m dari kampus</p>
        </div>

        @if ($today->checked_out_at)
            <div class="nb-card bg-sky p-6">
                <p class="font-bold">Absen pulang</p>
                <p class="font-display text-5xl font-extrabold">Pulang</p>
                <p class="mt-2 font-bold">{{ $today->checked_out_at->format('H:i') }} WIB · {{ $today->out_distance_m }} m dari kampus</p>
            </div>
        @else
            <div class="nb-card border-dashed bg-white p-6">
                <p class="font-bold">Absen pulang</p>
                <p class="font-display text-5xl font-extrabold">Belum</p>
                <p class="mt-2 font-bold">Dibuka {{ $setting->checkout_start }}–{{ $setting->checkout_end }} WIB</p>
            </div>
        @endif
    </div>
@endif

@if ($mode === 'done')
    <div class="nb-card bg-sun p-6">
        <h2 class="text-2xl">Absensi hari ini sudah lengkap</h2>
        <p class="mt-1 max-w-prose font-medium">Absen masuk dan pulang sudah tercatat. Sampai jumpa besok.</p>
        <a href="{{ route('student.history') }}" class="nb-btn mt-4 bg-white">Lihat riwayat</a>
    </div>
@elseif (! $hasFace)
    <div class="nb-card bg-bubble p-6">
        <h2 class="text-2xl">Daftarkan wajah dulu</h2>
        <p class="mt-1 max-w-prose font-medium">Wajah Anda belum terdaftar, jadi absensi belum bisa dilakukan. Pendaftaran hanya sekali dan butuh kamera depan.</p>
        <a href="{{ route('student.face.edit') }}" class="nb-btn mt-4 bg-white">Daftarkan wajah</a>
    </div>
@else
    {{-- mode 'in' = absen masuk, mode 'out' = absen pulang (alamat tujuan berbeda) --}}
    <div id="attendance-app" class="grid gap-6 lg:grid-cols-2"
         data-mode="{{ $mode }}"
         data-lat="{{ $setting->latitude }}" data-lng="{{ $setting->longitude }}"
         data-radius="{{ $setting->radius_m }}"
         data-url="{{ $mode === 'in' ? route('student.attendance.store') : route('student.attendance.checkout') }}">

        {{-- Kamera --}}
        <section id="capture-panel" class="nb-card space-y-4 p-4 sm:p-5">
            <h2 class="text-xl">1. Verifikasi wajah · {{ $mode === 'in' ? 'absen masuk' : 'absen pulang' }}</h2>

            <div id="video-wrap" class="hidden overflow-hidden rounded-md border-[3px] border-black bg-black">
                <video id="video" class="aspect-[4/3] w-full -scale-x-100 object-cover" playsinline muted></video>
            </div>
            <p id="face-state" class="font-bold">Kamera belum menyala.</p>
            <p id="message" class="hidden"></p>

            <button id="btn-start" type="button" class="nb-btn w-full bg-sky">Nyalakan kamera</button>
            <button id="btn-checkin" type="button" class="nb-btn w-full {{ $mode === 'in' ? 'bg-mint' : 'bg-tangerine' }} py-3 text-lg" disabled>
                {{ $mode === 'in' ? 'Absen masuk sekarang' : 'Absen pulang sekarang' }}
            </button>
            <p class="text-sm font-medium text-zinc-600">
                Tombol aktif jika wajah terdeteksi dan Anda berada dalam radius {{ $setting->radius_m }} m dari kampus.
                @if ($mode === 'out')
                    Absen pulang hanya diterima pukul {{ $setting->checkout_start }}–{{ $setting->checkout_end }} WIB.
                @endif
            </p>
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
@endsection

@if ($mode !== 'done' && $hasFace)
    @push('scripts')
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
        <script defer src="{{ config('absensi.face.script_url') }}"></script>
        @vite('resources/js/pages/attendance.js')
    @endpush
@endif