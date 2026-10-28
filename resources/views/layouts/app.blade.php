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
        <!-- ================= POPUP & HOVER PREVIEW FOTO NEOBRUTALISM ================= -->
    <style>
        /* Efek interaktif pada thumbnail foto di tabel */
        td img, a[href*="photo"] img {
            cursor: zoom-in;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        td img:hover, a[href*="photo"] img:hover {
            transform: scale(1.08);
            box-shadow: 3px 3px 0px #000;
        }

        /* Kotak Pop-up melayang saat kursor diarahkan (Hover) */
        #neo-hover-preview {
            position: fixed;
            z-index: 9998;
            pointer-events: none;
            display: none;
            background: #ffffff;
            border: 3px solid #000000;
            box-shadow: 6px 6px 0px #000000;
            border-radius: 12px;
            padding: 8px;
            width: 240px;
        }
        #neo-hover-preview img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border: 2px solid #000000;
            border-radius: 8px;
            background: #f3f4f6;
        }
        #neo-hover-preview .neo-caption {
            margin-top: 6px;
            background: #facc15;
            border: 2px solid #000000;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 800;
            text-align: center;
            color: #000000;
        }

        /* Modal Pop-up Besar saat Foto Diklik */
        #neo-click-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(3px);
            padding: 16px;
        }
        #neo-click-modal.active {
            display: flex;
        }
        .neo-modal-box {
            background: #ffffff;
            border: 4px solid #000000;
            box-shadow: 8px 8px 0px #000000;
            border-radius: 16px;
            max-width: 460px;
            width: 100%;
            overflow: hidden;
            animation: neoPop 0.15s ease-out;
        }
        @keyframes neoPop {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <!-- Elemen Pop-up Hover (Saat kursor di atas foto) -->
    <div id="neo-hover-preview">
        <img id="neo-hover-img" src="" alt="Preview Foto">
        <div class="neo-caption">Klik foto untuk memperbesar</div>
    </div>

    <!-- Elemen Pop-up Modal (Saat foto diklik) -->
    <div id="neo-click-modal" onclick="closeNeoModal(event)">
        <div class="neo-modal-box" onclick="event.stopPropagation()">
            <div style="background: #facc15; border-bottom: 3px solid #000; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                <span id="neo-modal-title" style="font-weight: 800; font-size: 15px; color: #000;">Detail Foto Absensi</span>
                <button type="button" onclick="closeNeoModal()" style="background: #f472b6; border: 2px solid #000; box-shadow: 2px 2px 0px #000; border-radius: 8px; padding: 4px 12px; font-weight: 800; cursor: pointer;">
                    Tutup ✕
                </button>
            </div>
            <div style="padding: 16px; background: #fff;">
                <img id="neo-modal-img" src="" alt="Foto Absensi" style="width: 100%; max-height: 400px; object-fit: contain; border: 3px solid #000; border-radius: 10px; background: #111;">
                <div id="neo-modal-info" style="margin-top: 10px; font-size: 13px; font-weight: 700; color: #111; text-align: center;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const hoverBox = document.getElementById('neo-hover-preview');
        const hoverImg = document.getElementById('neo-hover-img');
        const modal = document.getElementById('neo-click-modal');
        const modalImg = document.getElementById('neo-modal-img');
        const modalInfo = document.getElementById('neo-modal-info');

        // Cari semua gambar di dalam tabel absensi atau link photo
        const photos = document.querySelectorAll('table td img, a[href*="photo"] img');

        photos.forEach(function (img) {
            const parentLink = img.closest('a');
            const fullSrc = (parentLink && parentLink.getAttribute('href')) ? parentLink.getAttribute('href') : img.src;

            // Ambil informasi nama & tanggal dari baris tabel yang sama (jika ada)
            const row = img.closest('tr');
            let infoText = '';
            if (row) {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 7) {
                    const tanggal = cells[2]?.innerText.trim() || '';
                    const nim = cells[5]?.innerText.trim() || '';
                    const nama = cells[6]?.innerText.trim() || '';
                    infoText = nama + (nim ? ' (' + nim + ')' : '') + (tanggal ? ' — ' + tanggal : '');
                }
            }

            // 1. Saat kursor masuk ke area foto (Hover)
            img.addEventListener('mouseenter', function () {
                hoverImg.src = img.src;
                hoverBox.style.display = 'block';
            });

            // Ikuti posisi kursor mouse
            img.addEventListener('mousemove', function (e) {
                let left = e.clientX + 20;
                let top = e.clientY - 80;

                // Cegah pop-up keluar dari batas layar
                if (left + 260 > window.innerWidth) left = e.clientX - 260;
                if (top < 10) top = 10;
                if (top + 270 > window.innerHeight) top = window.innerHeight - 270;

                hoverBox.style.left = left + 'px';
                hoverBox.style.top = top + 'px';
            });

            // Saat kursor keluar dari foto
            img.addEventListener('mouseleave', function () {
                hoverBox.style.display = 'none';
            });

            // 2. Saat foto atau link-nya diklik (Buka Pop-up Modal & Cegah pindah tab baru)
            const clickTarget = parentLink || img;
            clickTarget.addEventListener('click', function (e) {
                e.preventDefault();
                hoverBox.style.display = 'none';
                modalImg.src = fullSrc || img.src;
                modalInfo.innerText = infoText;
                modal.classList.add('active');
            });
        });

        // Tutup dengan tombol ESC di keyboard
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeNeoModal();
        });
    });

    function closeNeoModal() {
        const modal = document.getElementById('neo-click-modal');
        if (modal) modal.classList.remove('active');
    }
    </script>
    <!-- =========================================================================== -->
</body>
</html>
