<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Absensi Yapparindo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-center items-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center space-y-4">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 font-bold text-xl">
            AY
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">SMK Yapparindo</h1>
        <p class="text-sm text-slate-500">Aplikasi Absensi & Jurnal Pembelajaran</p>
        <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-400">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Fondasi Arsitektur Aktif (Fase 2)</span>
        </div>
    </div>
</body>
</html>

