<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title') · Absensi STIKOM El Rahma</title>
    @stack('head')
</head>
<body>
@php
    $user = auth()->user();
    $home = $user->isAdmin() ? route('admin.dashboard') : route('student.attendance');

    // [label, route, pola route aktif, warna]
    $nav = $user->isAdmin() ? [
        ['Dashboard',         'admin.dashboard',        'admin.dashboard',     'bg-sky'],
        ['Mahasiswa',         'admin.students.index',   'admin.students.*',    'bg-bubble'],
        ['Laporan Absensi',   'admin.attendances.index','admin.attendances.*', 'bg-mint'],
        ['Pengaturan Lokasi', 'admin.settings.edit',    'admin.settings.*',    'bg-tangerine'],
    ] : [
        ['Absen',        'student.attendance', 'student.attendance*', 'bg-mint'],
        ['Riwayat',      'student.history',    'student.history',     'bg-sky'],
        ['Daftar Wajah', 'student.face.edit',  'student.face.*',      'bg-bubble'],
    ];
@endphp

<div class="min-h-dvh lg:flex">
    {{-- Sidebar desktop --}}
    <aside class="hidden border-r-[3px] border-black bg-sun p-5 lg:sticky lg:top-0 lg:flex lg:h-dvh lg:w-72 lg:shrink-0 lg:flex-col lg:gap-6">
        <a href="{{ $home }}" class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-md border-[3px] border-black bg-white font-display text-xl font-extrabold shadow-nb-sm">ER</span>
            <span class="font-display text-lg leading-tight font-extrabold">Absensi<br>STIKOM El Rahma</span>
        </a>

        <nav class="flex flex-col gap-2" aria-label="Menu utama">
            @foreach ($nav as [$label, $routeName, $pattern, $color])
                <a href="{{ route($routeName) }}"
                   class="nb-btn justify-start {{ request()->routeIs($pattern) ? 'bg-black text-white' : 'bg-white' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="nb-card mt-auto p-4">
            <p class="truncate font-bold">{{ $user->name }}</p>
            <p class="mb-3 truncate text-sm text-zinc-600">{{ $user->isAdmin() ? 'Administrator' : 'NIM '.$user->nim }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nb-btn w-full bg-bubble">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        {{-- Header & menu mobile/tablet --}}
        <header class="flex items-center justify-between gap-3 border-b-[3px] border-black bg-sun px-4 py-3 lg:hidden">
            <a href="{{ $home }}" class="flex items-center gap-2">
                <span class="grid size-10 place-items-center rounded-md border-[3px] border-black bg-white font-display font-extrabold">ER</span>
                <span class="font-display font-extrabold leading-tight">STIKOM El Rahma</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nb-btn min-h-10 bg-bubble px-3 py-1 text-sm">Keluar</button>
            </form>
        </header>
        <nav class="sticky top-0 z-20 flex gap-2 overflow-x-auto border-b-[3px] border-black bg-white px-4 py-3 lg:hidden" aria-label="Menu utama">
            @foreach ($nav as [$label, $routeName, $pattern, $color])
                <a href="{{ route($routeName) }}"
                   class="nb-btn min-h-10 shrink-0 px-3 py-1 text-sm {{ request()->routeIs($pattern) ? 'bg-black text-white' : $color }}">{{ $label }}</a>
            @endforeach
        </nav>

        <main class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-3xl sm:text-4xl">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="mt-1 max-w-prose font-medium text-zinc-700">@yield('subheading')</p>
                @endif
            </div>

            {{-- Jam real-time WIB. Isi awal dari server agar tidak berkedip saat halaman dimuat. --}}
            <div data-clock data-server-ms="{{ now()->getTimestamp() * 1000 }}"
                class="nb-card shrink-0 bg-sun px-4 py-3 sm:text-right" aria-live="off">
                <p class="font-display text-3xl font-extrabold tabular-nums leading-none sm:text-4xl">
                    <span data-clock-time>{{ now()->format('H:i:s') }}</span>
                    <span class="text-base">WIB</span>
                </p>
                <p data-clock-date class="mt-1 text-sm font-bold">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
