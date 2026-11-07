@extends('layouts.app')
@section('title', 'Absen')
@section('heading', 'Halo, '.explode(' ', auth()->user()->name)[0])

@php
$masukWindow = $setting->start_time.'–'.$setting->end_time.' WIB';
$pulangWindow = $setting->checkout_start.'–'.$setting->checkout_end.' WIB';

// Subjudul mengikuti kondisi: belum masuk, sudah masuk, atau sudah lengkap.
$subheading = now()->translatedFormat('l, d F Y').' · '.match ($mode) {
'in' => 'Absen masuk dibuka '.$masukWindow.', tepat waktu sampai '.$setting->on_time_until.' WIB.',
'out' => 'Sudah absen masuk. Absen pulang dibuka '.$pulangWindow.'.',
default => 'Absen masuk dan absen pulang hari ini sudah lengkap.',
};
@endphp
@section('subheading', $subheading)

@if (($mode === 'in' || $mode === 'out') && $hasFace)
@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
@endpush
@endif

@section('content')
@if ($mode === 'done')
{{-- ============ KONDISI 3: sudah masuk & sudah pulang ============ --}}
<div class="nb-card bg-mint p-6 sm:p-8">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-bold">Absensi hari ini selesai</p>
            <p class="font-display text-4xl font-extrabold sm:text-5xl">Terima kasih!</p>
        </div>
        @include('partials.status-badge', ['status' => $today->status])
    </div>

    {{-- Rekap jam masuk dan jam pulang --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-md border-[3px] border-black bg-white p-4">
            <p class="text-sm font-bold text-zinc-600">Jam masuk</p>
            <p class="font-display text-4xl font-extrabold">
                {{ $today->checked_in_at->format('H:i') }} <span class="text-lg">WIB</span>
            </p>
            <p class="mt-1 text-sm font-bold">{{ $today->status_label }} · {{ $today->distance_m }} m dari kampus</p>
        </div>
        <div class="rounded-md border-[3px] border-black bg-white p-4">
            <p class="text-sm font-bold text-zinc-600">Jam pulang</p>
            <p class="font-display text-4xl font-extrabold">
                {{ $today->checked_out_at->format('H:i') }} <span class="text-lg">WIB</span>
            </p>
            <p class="mt-1 text-sm font-bold">Pulang · {{ $today->out_distance_m }} m dari kampus</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('student.history') }}" class="nb-btn bg-sky">Lihat riwayat</a>
        <a href="{{ route('student.face.edit') }}" class="nb-btn bg-white">Data wajah</a>
    </div>
</div>
@else
{{-- ============ KONDISI 1 & 2: form kamera + GPS tetap aktif ============ --}}
@if ($mode === 'out')
{{-- Ringkasan absen masuk + status jendela absen pulang --}}
<div class="mb-6 grid gap-4 md:grid-cols-2">
    <div class="nb-card {{ $today->status === 'terlambat' ? 'bg-tangerine' : 'bg-mint' }} p-5">
        <p class="text-sm font-bold text-zinc-700">Absen masuk</p>
        <p class="font-display text-4xl font-extrabold">
            {{ $today->checked_in_at->format('H:i') }} <span class="text-lg">WIB</span>
        </p>
        <p class="mt-1 text-sm font-bold">{{ $today->status_label }} · {{ $today->distance_m }} m dari kampus</p>
    </div>

    <div class="nb-card bg-white p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-zinc-700">Absen pulang</p>
                <p class="font-display text-4xl font-extrabold">Belum</p>
            </div>

            {{-- Badge status jendela pulang (dihitung server, WIB) --}}
            @if ($checkoutState === 'open')
            <span class="rounded-md border-[3px] border-black bg-mint px-2 py-1 text-xs font-extrabold uppercase shadow-nb-sm">Dibuka</span>
            @elseif ($checkoutState === 'before')
            <span class="rounded-md border-[3px] border-black bg-sun px-2 py-1 text-xs font-extrabold uppercase shadow-nb-sm">Belum dibuka</span>
            @else
            <span class="rounded-md border-[3px] border-black bg-bubble px-2 py-1 text-xs font-extrabold uppercase shadow-nb-sm">Ditutup</span>
            @endif
        </div>

        <p class="mt-2 text-sm font-bold">
            Absen pulang: {{ $pulangWindow }}
            @if ($checkoutState === 'before')
            · Dibuka pukul {{ $setting->checkout_start }} WIB
            @elseif ($checkoutState === 'after')
            · Sudah ditutup pukul {{ $setting->checkout_end }} WIB
            @endif
        </p>
    </div>
</div>
@else
{{-- Kondisi 1: belum absen masuk sama sekali --}}
<div class="nb-card mb-6 bg-white p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="font-bold">Belum absen hari ini</p>
            <p class="text-sm font-medium text-zinc-700">
                Absen masuk dibuka {{ $masukWindow }}, tepat waktu sampai {{ $setting->on_time_until }} WIB.
            </p>
        </div>
        <span class="rounded-md border-[3px] border-black bg-sun px-3 py-1 text-xs font-extrabold uppercase shadow-nb-sm">Belum absen</span>
    </div>
</div>
@endif

@if (! $hasFace)
<div class="nb-card bg-bubble p-6">
    <h2 class="text-2xl">Daftarkan wajah dulu</h2>
    <p class="mt-1 max-w-prose font-medium">Wajah Anda belum terdaftar, jadi absensi belum bisa dilakukan. Pendaftaran hanya sekali dan butuh kamera depan.</p>
    <a href="{{ route('student.face.edit') }}" class="nb-btn mt-4 bg-white">Daftarkan wajah</a>
</div>
@else
<div id="attendance-app" class="grid gap-6 lg:grid-cols-2"
    data-mode="{{ $mode }}"
    data-lat="{{ $setting->latitude }}" data-lng="{{ $setting->longitude }}"
    data-radius="{{ $setting->radius_m }}"
    data-url="{{ $mode === 'out' ? route('student.attendance.checkout') : route('student.attendance.store') }}">

    {{-- Kamera --}}
    <section id="capture-panel" class="nb-card space-y-4 p-4 sm:p-5">
        <h2 class="text-xl">1. Verifikasi Wajah · {{ $mode === 'out' ? 'absen pulang' : 'absen masuk' }}</h2>

        <div id="video-wrap" class="hidden overflow-hidden rounded-md border-[3px] border-black bg-black">
            <video id="video" class="aspect-[4/3] w-full -scale-x-100 object-cover" playsinline muted></video>
        </div>
        <p id="face-state" class="font-bold">Kamera belum menyala.</p>
        <p id="message" class="hidden"></p>

        <button id="btn-start" type="button" class="nb-btn w-full bg-sky">Nyalakan kamera</button>
        <button id="btn-attendance" type="button"
            class="nb-btn w-full py-3 text-lg {{ $mode === 'out' ? 'bg-tangerine' : 'bg-mint' }}" disabled>
            {{ $mode === 'out' ? 'Absen pulang sekarang' : 'Absen masuk sekarang' }}
        </button>
        <p class="text-sm font-medium text-zinc-600">
            Tombol aktif jika wajah terdeteksi dan Anda berada dalam radius {{ $setting->radius_m }} m dari kampus.
            @if ($mode === 'out')
            Absen pulang hanya diterima pukul {{ $pulangWindow }}.
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
@endif
@endsection

@if (($mode === 'in' || $mode === 'out') && $hasFace)
@push('scripts')
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script defer src="{{ config('absensi.face.script_url') }}"></script>
@vite('resources/js/pages/attendance.js')
@endpush
@endif