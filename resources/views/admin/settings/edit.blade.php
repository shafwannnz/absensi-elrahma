@extends('layouts.app')
@section('title', 'Pengaturan Lokasi')
@section('heading', 'Pengaturan Lokasi & Jam')
@section('subheading', 'Klik peta atau seret pin untuk menentukan titik kampus. Lingkaran menunjukkan radius absensi.')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
@endpush

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-6 lg:grid-cols-5">
    @csrf @method('PUT')

    {{-- Peta + pin point --}}
    <section class="nb-card space-y-3 p-4 lg:col-span-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl">Pin point lokasi</h2>
            <button type="button" id="btn-locate" class="nb-btn min-h-10 bg-sky px-3 py-1 text-sm">Gunakan lokasi saya</button>
        </div>
        <div id="map" class="z-0 h-80 rounded-md border-[3px] border-black sm:h-[26rem]"></div>
        <p id="map-status" class="hidden"></p>
    </section>

    {{-- Input angka --}}
    <section class="nb-card space-y-4 p-4 sm:p-5 lg:col-span-2">
        <div>
            <label for="name" class="nb-label">Nama lokasi</label>
            <input id="name" name="name" value="{{ old('name', $setting->name) }}" class="nb-input" required>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <div>
                <label for="latitude" class="nb-label">Latitude</label>
                <input id="latitude" name="latitude" type="number" step="any" value="{{ old('latitude', $setting->latitude) }}" class="nb-input" required>
            </div>
            <div>
                <label for="longitude" class="nb-label">Longitude</label>
                <input id="longitude" name="longitude" type="number" step="any" value="{{ old('longitude', $setting->longitude) }}" class="nb-input" required>
            </div>
        </div>
        <div>
            <label for="radius_m" class="nb-label">Radius maksimal (meter)</label>
            <input id="radius_m" name="radius_m" type="number" min="50" max="5000" value="{{ old('radius_m', $setting->radius_m) }}" class="nb-input" required>
        </div>

        <div class="rounded-md border-[3px] border-black bg-sun p-3">
            <p class="mb-3 font-bold">Jam absensi (WIB)</p>
            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                <div>
                    <label for="start_time" class="nb-label">Dibuka</label>
                    <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $setting->start_time) }}" class="nb-input" required>
                </div>
                <div>
                    <label for="on_time_until" class="nb-label">Tepat waktu s.d.</label>
                    <input id="on_time_until" name="on_time_until" type="time" value="{{ old('on_time_until', $setting->on_time_until) }}" class="nb-input" required>
                </div>
                <div>
                    <label for="end_time" class="nb-label">Ditutup</label>
                    <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $setting->end_time) }}" class="nb-input" required>
                </div>
            </div>
            <p class="mt-3 text-sm font-medium">Absen antara jam dibuka dan batas tepat waktu = <b>Hadir</b>. Setelahnya sampai jam ditutup = <b>Terlambat</b>.</p>
        </div>
        <div class="rounded-md border-[3px] border-black bg-sky/30 p-3">
        <p class="mb-3 font-bold">Jam absen pulang (WIB)</p>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <div>
                <label for="checkout_start" class="nb-label">Dibuka</label>
                <input id="checkout_start" name="checkout_start" type="time" value="{{ old('checkout_start', $setting->checkout_start) }}" class="nb-input" required>
            </div>
            <div>
                <label for="checkout_end" class="nb-label">Ditutup</label>
                <input id="checkout_end" name="checkout_end" type="time" value="{{ old('checkout_end', $setting->checkout_end) }}" class="nb-input" required>
            </div>
        </div>
        <p class="mt-3 text-sm font-medium">Mahasiswa hanya bisa absen pulang setelah absen masuk, dan di antara jam dibuka dan ditutup.</p>
    </div>
        <button class="nb-btn w-full bg-mint">Simpan pengaturan</button>
    </section>
</form>
@endsection

@push('scripts')
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    @vite('resources/js/pages/settings-map.js')
@endpush
