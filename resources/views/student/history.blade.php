@extends('layouts.app')
@section('title', 'Riwayat')
@section('heading', 'Riwayat Absensi')
@section('subheading', 'Catatan kehadiran Anda dari yang terbaru.')

@section('content')
<div class="grid gap-4 sm:grid-cols-3">
    <div class="nb-card bg-sky p-5"><p class="font-bold">Total absen</p><p class="font-display text-5xl font-extrabold">{{ $summary['total'] }}</p></div>
    <div class="nb-card bg-mint p-5"><p class="font-bold">Hadir tepat waktu</p><p class="font-display text-5xl font-extrabold">{{ $summary['present'] }}</p></div>
    <div class="nb-card bg-tangerine p-5"><p class="font-bold">Terlambat</p><p class="font-display text-5xl font-extrabold">{{ $summary['late'] }}</p></div>
</div>

<div class="nb-card mt-6 p-4 sm:p-5">
    <div class="overflow-x-auto">
        <table class="nb-table">
            <thead><tr><th>Tanggal</th><th>Jam</th><th>Status</th><th>Jarak dari kampus</th></tr></thead>
            <tbody>
            @forelse ($attendances as $a)
                <tr>
                    <td class="whitespace-nowrap font-bold">{{ $a->attended_on->translatedFormat('l, d M Y') }}</td>
                    <td>{{ $a->checked_in_at->format('H:i') }} WIB</td>
                    <td>@include('partials.status-badge', ['status' => $a->status])</td>
                    <td>{{ $a->distance_m }} m</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-8 text-center font-bold">
                    Belum ada riwayat. <a href="{{ route('student.attendance') }}" class="underline">Absen sekarang</a>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $attendances->links('vendor.pagination.neo') }}
</div>
@endsection
