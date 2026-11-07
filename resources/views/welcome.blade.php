@extends('layouts.guest')
@section('title', 'Selamat Datang')
@section('wide', true)
@section('maxWidth', 'max-w-6xl')

@php
$max = max(1, collect($chart)->map(fn ($d) => $d['present'] + $d['late'])->max());
@endphp

@section('content')
<div class="w-full space-y-5 sm:space-y-6">

    {{-- ========== HEADER / HERO ========== --}}
    <section class="nb-card bg-sun p-5 sm:p-8">
        <div class="flex items-start gap-3 sm:gap-4">
            <span class="grid size-12 shrink-0 -rotate-3 place-items-center rounded-md border-[3px] border-black bg-white font-display text-xl font-extrabold shadow-nb-sm sm:size-14 sm:text-2xl">ER</span>
            <div class="min-w-0">
                <h1 class="text-2xl leading-tight sm:text-3xl lg:text-4xl">Sistem Informasi Absensi</h1>
                <p class="text-base font-bold sm:text-lg">Sekolah Tinggi Ilmu Komputer El Rahma</p>
            </div>
        </div>
        <p class="mt-4 max-w-xl text-sm font-medium text-zinc-700 sm:text-base">
            Sistem absensi berbasis pengenalan wajah dan lokasi GPS.
            Mahasiswa cukup membuka halaman absen, memverifikasi wajah,
            dan kehadiran langsung tercatat secara otomatis.
        </p>

        {{-- Tombol aksi --}}
        <div class="mt-5 flex flex-col gap-3 sm:mt-6 sm:flex-row">
            <a href="{{ route('login') }}" class="nb-btn bg-sky text-white">Masuk sebagai Mahasiswa</a>
            <a href="{{ route('admin.login') }}" class="nb-btn bg-white">Masuk sebagai Admin</a>
        </div>
    </section>

    {{-- ========== FITUR SISTEM ========== --}}
    <section class="nb-card p-5 sm:p-8">
        <h2 class="text-xl text-center sm:text-2xl">Fitur Sistem</h2>
        <p class="mt-1 text-center text-sm font-medium text-zinc-600 sm:text-base">Teknologi yang digunakan dalam sistem absensi ini.</p>

        <div class="mt-5 grid gap-3 sm:mt-6 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
            @php
            $features = [
            ['🤳', 'bg-sky', 'Pengenalan Wajah', 'Verifikasi identitas otomatis menggunakan teknologi face recognition berbasis AI.'],
            ['📍', 'bg-mint', 'Validasi Lokasi GPS', 'Memastikan mahasiswa berada di area kampus saat melakukan absensi.'],
            ['⏰', 'bg-tangerine', 'Pencatatan Waktu', 'Sistem otomatis mencatat jam masuk dan pulang serta mendeteksi keterlambatan.'],
            ['📊', 'bg-bubble', 'Laporan Real-time', 'Dashboard admin dengan grafik dan tabel kehadiran yang selalu diperbarui.'],
            ['📱', 'bg-sun', 'Progressive Web App', 'Bisa diakses dari browser HP dan dipasang di layar utama seperti aplikasi native.'],
            ['🔒', 'bg-sky', 'Keamanan Data', 'Login terpisah untuk mahasiswa dan admin dengan enkripsi data sensitif.'],
            ];
            @endphp

            @foreach ($features as [$icon, $color, $title, $desc])
            <div class="nb-card {{ $color }} p-4 sm:p-5">
                <span class="text-2xl sm:text-3xl">{{ $icon }}</span>
                <h3 class="mt-1.5 text-base sm:mt-2 sm:text-lg">{{ $title }}</h3>
                <p class="mt-1 text-xs font-medium sm:text-sm">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ========== CARA KERJA ========== --}}
    <section class="nb-card bg-white p-5 sm:p-8">
        <h2 class="text-xl text-center sm:text-2xl">Cara Kerja</h2>
        <p class="mt-1 text-center text-sm font-medium text-zinc-600 sm:text-base">Langkah-langkah absensi bagi mahasiswa.</p>

        <div class="mt-5 grid gap-3 grid-cols-2 sm:mt-6 sm:gap-4 lg:grid-cols-4">
            @php
            $steps = [
            ['1', 'bg-sky', 'Login', 'Masuk ke sistem menggunakan NIM dan kata sandi Anda.'],
            ['2', 'bg-mint', 'Izinkan Lokasi', 'Berikan akses lokasi agar sistem memverifikasi posisi Anda.'],
            ['3', 'bg-tangerine', 'Foto Wajah', 'Arahkan kamera ke wajah untuk verifikasi identitas otomatis.'],
            ['4', 'bg-bubble', 'Selesai!', 'Absensi tercatat. Lihat riwayat kehadiran kapan saja.'],
            ];
            @endphp

            @foreach ($steps as [$num, $color, $title, $desc])
            <div class="nb-card {{ $color }} p-4 text-center sm:p-5">
                <span class="inline-grid size-10 place-items-center rounded-full border-[3px] border-black bg-white font-display text-lg font-extrabold shadow-nb-sm sm:size-12 sm:text-xl">{{ $num }}</span>
                <h3 class="mt-2 text-base sm:mt-3 sm:text-lg">{{ $title }}</h3>
                <p class="mt-1 text-xs font-medium sm:text-sm">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer class="nb-card bg-sun p-4 text-center sm:p-5">
        <p class="text-sm font-bold sm:text-base">Sistem Informasi Absensi · STIKOM El Rahma</p>
        <p class="mt-1 text-xs font-medium text-zinc-700 sm:text-sm">&copy; {{ date('Y') }} — Dibangun dengan teknologi Face Recognition & GPS.</p>
    </footer>
</div>
@endsection