@extends('layouts.guest')

@php $isAdmin = $role === 'admin'; @endphp
@section('title', $isAdmin ? 'Login Admin' : 'Login Mahasiswa')

@section('content')
<div class="grid w-full gap-6 md:grid-cols-5">
    {{-- Panel judul --}}
    <section class="nb-card flex flex-col justify-between gap-8 bg-sun p-6 md:col-span-3 sm:p-8">
        <span class="grid size-16 -rotate-3 place-items-center rounded-md border-[3px] border-black bg-white font-display text-3xl font-extrabold shadow-nb-sm">ER</span>
        <div>
            <h1 class="text-4xl leading-none sm:text-5xl">Sistem Informasi Absensi Mahasiswa</h1>
            <p class="mt-3 text-lg font-bold">Sekolah Tinggi Ilmu Komputer El Rahma</p>
            <p class="mt-4 max-w-md font-medium">
                {{ $isAdmin
                    ? 'Kelola data mahasiswa, pantau kehadiran, dan atur lokasi serta jam absensi.'
                    : 'Absen cukup dengan wajah dan lokasi. Pastikan Anda berada di sekitar kampus.' }}
            </p>
        </div>
    </section>

    {{-- Form login --}}
    <section class="nb-card p-6 md:col-span-2 sm:p-8">
        <h2 class="mb-5 text-2xl">{{ $isAdmin ? 'Masuk sebagai admin' : 'Masuk sebagai mahasiswa' }}</h2>

        <form method="POST" action="{{ $isAdmin ? route('admin.login.attempt') : route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label for="identity" class="nb-label">{{ $isAdmin ? 'Email' : 'NIM' }}</label>
                <input id="identity" name="{{ $isAdmin ? 'email' : 'nim' }}" type="{{ $isAdmin ? 'email' : 'text' }}"
                       inputmode="{{ $isAdmin ? 'email' : 'numeric' }}"
                       value="{{ old($isAdmin ? 'email' : 'nim') }}" class="nb-input" required autofocus autocomplete="username">
                @error($isAdmin ? 'email' : 'nim') <p class="nb-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="nb-label">Kata sandi</label>
                <input id="password" name="password" type="password" class="nb-input" required autocomplete="current-password">
                @error('password') <p class="nb-error">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 font-medium">
                <input type="checkbox" name="remember" class="size-5 accent-black"> Ingat saya
            </label>
            <button class="nb-btn w-full bg-sky text-white">Masuk</button>
        </form>

        <p class="mt-5 text-sm font-medium">
            @if ($isAdmin)
                Mahasiswa? <a href="{{ route('login') }}" class="font-bold underline">Login di sini</a>
            @else
                Belum punya akun? Hubungi admin akademik. Pendaftaran dilakukan oleh admin.<br>
                <a href="{{ route('admin.login') }}" class="font-bold underline">Login admin</a>
            @endif
        </p>
    </section>
</div>
@endsection
