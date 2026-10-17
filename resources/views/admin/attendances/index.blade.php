@extends('layouts.app')
@section('title', 'Laporan Absensi')
@section('heading', 'Laporan Absensi')
@section('subheading', 'Filter berdasarkan tanggal, status, atau mahasiswa. Hasilnya bisa diunduh sebagai CSV.')

@section('content')
<div class="nb-card p-4 sm:p-5">
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label for="from" class="nb-label">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="nb-input">
        </div>
        <div>
            <label for="to" class="nb-label">Sampai tanggal</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="nb-input">
        </div>
        <div>
            <label for="status" class="nb-label">Status</label>
            <select id="status" name="status" class="nb-input">
                <option value="">Semua</option>
                <option value="hadir" @selected(($filters['status'] ?? '') === 'hadir')>Hadir</option>
                <option value="terlambat" @selected(($filters['status'] ?? '') === 'terlambat')>Terlambat</option>
            </select>
        </div>
        <div>
            <label for="q" class="nb-label">Nama / NIM</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="nb-input">
        </div>
        <div class="flex items-end gap-2">
            <button class="nb-btn flex-1 bg-sky">Terapkan</button>
            <a href="{{ route('admin.attendances.export', $filters) }}" class="nb-btn bg-mint">CSV</a>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="nb-table">
            <thead><tr><th>Foto masuk</th><th>Foto pulang</th><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>NIM</th><th>Nama</th><th>Status</th><th>Jarak</th><th>Akurasi GPS</th></tr></thead>
            <tbody>
            @forelse ($attendances as $a)
                <tr>
                    <td>@include('partials.selfie', ['attendance' => $a, 'type' => 'in'])</td>
                    <td>@include('partials.selfie', ['attendance' => $a, 'type' => 'out'])</td>
                    <td class="whitespace-nowrap">{{ $a->attended_on->translatedFormat('d M Y') }}</td>
                    <td>{{ $a->checked_in_at->format('H:i') }}</td>
                    <td>{{ $a->checked_out_at?->format('H:i') ?? '-' }}</td>
                    <td>{{ $a->user->nim }}</td>
                    <td class="font-bold">{{ $a->user->name }}</td>
                    <td>@include('partials.status-badge', ['status' => $a->status])</td>
                    <td>{{ $a->distance_m }} m</td>
                    <td>{{ $a->accuracy_m !== null ? '±'.$a->accuracy_m.' m' : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="py-8 text-center font-bold">Tidak ada data absensi untuk filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $attendances->links('vendor.pagination.neo') }}
</div>
@endsection
