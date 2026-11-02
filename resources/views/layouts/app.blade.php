<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Absensi & Jurnal') — SMK Yapparindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-800 flex flex-col justify-between">
    <div class="flex-1 flex flex-col">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>

