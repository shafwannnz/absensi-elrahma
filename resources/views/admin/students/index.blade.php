@extends('layouts.app')
@section('title', 'Mahasiswa')
@section('heading', 'Data Mahasiswa')
@section('subheading', 'Admin membuat akun mahasiswa dan mendaftarkan wajahnya. Mahasiswa tidak bisa mendaftar sendiri.')

@section('content')
<div class="nb-card p-4 sm:p-5">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 gap-2 sm:max-w-md">
            <input name="q" value="{{ $search }}" placeholder="Cari nama, NIM, atau prodi" class="nb-input" aria-label="Cari mahasiswa">
            <button class="nb-btn bg-sky">Cari</button>
        </form>
        <a href="{{ route('admin.students.create') }}" class="nb-btn">+ Tambah mahasiswa</a>
    </div>

    <div class="overflow-x-auto">
        <table class="nb-table">
            <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Wajah</th><th>Akun</th><th>Absen</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($students as $s)
                <tr>
                    <td>{{ $s->nim }}</td>
                    <td><span class="font-bold">{{ $s->name }}</span><br><span class="text-sm text-zinc-600">{{ $s->email }}</span></td>
                    <td>{{ $s->study_program ?? '-' }}</td>
                    <td><span class="nb-badge {{ $s->hasFace() ? 'bg-mint' : 'bg-bubble' }}">{{ $s->hasFace() ? 'Terdaftar' : 'Belum' }}</span></td>
                    <td><span class="nb-badge {{ $s->is_active ? 'bg-sky' : 'bg-zinc-300' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>{{ $s->attendances_count }}x</td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.students.edit', $s) }}" class="nb-btn min-h-9 bg-white px-3 py-1 text-sm">Ubah</a>
                            @if ($s->hasFace())
                                <form method="POST" action="{{ route('admin.students.face.reset', $s) }}" onsubmit="return confirm('Reset data wajah {{ $s->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="nb-btn min-h-9 bg-tangerine px-3 py-1 text-sm">Reset wajah</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.students.destroy', $s) }}" onsubmit="return confirm('Hapus {{ $s->name }} beserta riwayat absensinya?')">
                                @csrf @method('DELETE')
                                <button class="nb-btn min-h-9 bg-bubble px-3 py-1 text-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center font-bold">
                    {{ $search ? 'Tidak ada mahasiswa yang cocok dengan pencarian.' : 'Belum ada mahasiswa. Klik "Tambah mahasiswa" untuk memulai.' }}
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $students->links('vendor.pagination.neo') }}
</div>
@endsection
