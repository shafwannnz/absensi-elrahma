<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Masuk') · Absensi STIKOM El Rahma</title>
</head>
<body>
    <main class="mx-auto flex min-h-dvh max-w-5xl items-center p-4 sm:p-8">
        @yield('content')
    </main>
</body>
</html>
