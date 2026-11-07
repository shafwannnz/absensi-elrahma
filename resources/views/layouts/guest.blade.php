<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Masuk') · Absensi STIKOM El Rahma</title>
</head>
<body>
    <main class="mx-auto flex min-h-dvh @yield('maxWidth', 'max-w-5xl') @hasSection('wide') py-6 @else items-center @endif p-4 sm:p-8">
        @yield('content')
    </main>
</body>
</html>
