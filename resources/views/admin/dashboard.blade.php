@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Ringkasan kehadiran mahasiswa hari ini.')

@section('content')
@php
    $cards = [
        ['Total absensi',        $stats['total_attendance'], 'Seluruh catatan',          'bg-sky'],
        ['Hadir hari ini',       $stats['present_today'],    'Tepat waktu',               'bg-mint'],
        ['Terlambat hari ini',   $stats['late_today'],       'Lewat '.$setting->on_time_until.' WIB', 'bg-tangerine'],
        ['Mahasiswa terdaftar',  $stats['total_students'],   $stats['absent_today'].' belum absen hari ini', 'bg-bubble'],
    ];
    $max = max(1, collect($chart)->map(fn ($d) => $d['present'] + $d['late'])->max());
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as [$label, $value, $hint, $color])
        <div class="nb-card {{ $color }} p-5">
            <p class="font-bold">{{ $label }}</p>
            <p class="font-display text-5xl font-extrabold">{{ number_format($value) }}</p>
            <p class="text-sm font-bold">{{ $hint }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-5">
    {{-- Grafik 7 hari --}}
    <section class="nb-card p-5 lg:col-span-3">
        <h2 class="text-xl">Kehadiran 7 hari terakhir</h2>
        <div class="mt-4 flex h-48 items-end gap-2 sm:gap-3" role="img" aria-label="Grafik batang kehadiran 7 hari terakhir">
            @foreach ($chart as $day)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                    <span class="text-xs font-bold">{{ $day['present'] + $day['late'] }}</span>
                    <div class="flex w-full flex-col justify-end overflow-hidden rounded-t-md border-[3px] border-black" style="height: {{ (($day['present'] + $day['late']) / $max) * 100 }}%; min-height: 4px">
                        @if ($day['late'])<div class="bg-tangerine" style="flex: {{ $day['late'] }}"></div>@endif
                        @if ($day['present'])<div class="bg-mint" style="flex: {{ $day['present'] }}"></div>@endif
                    </div>
                    <span class="text-[11px] font-bold sm:text-xs">{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex gap-4 text-sm font-bold">
            <span class="flex items-center gap-2"><i class="size-4 border-2 border-black bg-mint"></i> Hadir</span>
            <span class="flex items-center gap-2"><i class="size-4 border-2 border-black bg-tangerine"></i> Terlambat</span>
        </div>
    </section>

    {{-- Ringkasan pengaturan --}}
    <section class="nb-card flex flex-col gap-3 bg-sun p-5 lg:col-span-2">
        <h2 class="text-xl">Aturan absensi aktif</h2>
        <dl class="space-y-2 font-medium">
            <div class="flex justify-between gap-3"><dt>Lokasi</dt><dd class="text-right font-bold">{{ $setting->name }}</dd></div>
            <div class="flex justify-between gap-3"><dt>Radius</dt><dd class="font-bold">{{ $setting->radius_m }} m</dd></div>
            <div class="flex justify-between gap-3"><dt>Absen masuk dibuka</dt><dd class="font-bold">{{ $setting->start_time }} WIB</dd></div>
            <div class="flex justify-between gap-3"><dt>Tepat waktu s.d.</dt><dd class="font-bold">{{ $setting->on_time_until }} WIB</dd></div>
            <div class="flex justify-between gap-3"><dt>Absen masuk ditutup</dt><dd class="font-bold">{{ $setting->end_time }} WIB</dd></div>
            <div class="flex justify-between gap-3"><dt>Absen pulang dibuka</dt><dd class="font-bold">{{ $setting->checkout_start }} WIB</dd></div>
            <div class="flex justify-between gap-3"><dt>Absen pulang ditutup</dt><dd class="font-bold">{{ $setting->checkout_end }} WIB</dd></div>
        </dl>
        <a href="{{ route('admin.settings.edit') }}" class="nb-btn mt-auto bg-white">Ubah pengaturan</a>
    </section>
</div>

{{-- Absensi terbaru --}}
<section class="nb-card mt-6 p-5">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xl">Absensi terbaru</h2>
        <a href="{{ route('admin.attendances.index') }}" class="font-bold underline">Lihat semua</a>
    </div>
    <div class="overflow-x-auto">
        <table class="nb-table">
            <thead><tr><th>Foto</th><th>Masuk</th><th>Pulang</th><th>NIM</th><th>Nama</th><th>Status</th><th>Jarak</th></tr></thead>
            <tbody>
            @forelse ($recent as $a)
                <tr>
                    <td>@include('partials.selfie', ['attendance' => $a, 'type' => 'in'])</td>
                    <td class="whitespace-nowrap">{{ $a->checked_in_at->translatedFormat('d M, H:i') }}</td>
                    <td>{{ $a->checked_out_at?->format('H:i') ?? '-' }}</td>
                    <td>{{ $a->user->nim }}</td>
                    <td class="font-bold">{{ $a->user->name }}</td>
                    <td>@include('partials.status-badge', ['status' => $a->status])</td>
                    <td>{{ $a->distance_m }} m</td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center font-bold">Belum ada absensi. Data akan muncul setelah mahasiswa absen.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
