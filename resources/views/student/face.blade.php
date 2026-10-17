@extends('layouts.app')
@section('title', 'Daftar Wajah')
@section('heading', 'Daftar Wajah')
@section('subheading', 'Wajah dipakai untuk memverifikasi absensi. Data berupa vektor angka, bukan foto.')

@section('content')
@if ($hasFace)
    <div class="nb-card bg-mint p-6">
        <h2 class="text-2xl">Wajah sudah terdaftar</h2>
        <p class="mt-1 max-w-prose font-medium">Untuk menjaga keamanan, data wajah hanya bisa diganti oleh admin. Hubungi admin akademik jika perlu mendaftar ulang.</p>
        <a href="{{ route('student.attendance') }}" class="nb-btn mt-4 bg-white">Kembali ke absen</a>
    </div>
@else
    <form method="POST" action="{{ route('student.face.store') }}" class="max-w-xl space-y-5">
        @csrf
        @include('partials.face-capture', ['hasFace' => false])
        <button class="nb-btn bg-mint">Simpan wajah</button>
    </form>

    @push('scripts')
        <script defer src="{{ config('absensi.face.script_url') }}"></script>
        @vite('resources/js/pages/enroll.js')
    @endpush
@endif
@endsection
