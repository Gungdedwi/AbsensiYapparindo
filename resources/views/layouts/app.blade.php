<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#059669">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <title>@yield('title', 'Absensi & Jurnal') — SMK Yapparindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-800 flex flex-col justify-between">
    <!-- Banner Status Jaringan Global (Online/Offline) -->
    <div id="networkStatusBar" class="hidden px-4 py-2 text-center text-xs font-semibold sticky top-0 z-50 transition shadow-sm"></div>

    <div class="flex-1 flex flex-col">
        @yield('content')
    </div>

    <script>
        window.currentUserId = {{ auth()->id() ?? 'null' }};

        // Daftarkan PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((err) => {
                    console.log('SW registration error:', err);
                });
            });
        }
    </script>
    <script src="/js/offline-sync.js"></script>

    @yield('scripts')
</body>
</html>
