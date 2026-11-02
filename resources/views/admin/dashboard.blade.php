@extends('layouts.app')

@section('title', 'Dashboard Admin Sekolah')

@section('content')
<div class="min-h-full bg-slate-50 pb-12">
    <!-- Header Admin -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-base shadow-sm">
                    AY
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">Admin Operasional Sekolah</h1>
                    <p class="text-[11px] text-slate-500">SMK Yapparindo</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden sm:inline-block text-xs text-slate-600 font-medium">
                    {{ $user->name }}
                </span>

                <!-- Tombol Logout -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 border border-rose-200 transition min-h-[38px] cursor-pointer"
                        title="Keluar dari sistem"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Konten Dashboard Admin -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 space-y-6">
        <!-- Banner Sambutan & Tanggal WITA -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                    Administrator
                </span>
                <h2 class="text-xl font-bold text-slate-900 mt-1">Selamat Datang, {{ $user->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau kesiapan fondasi sistem dan operasional akademik sekolah.</p>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 self-start sm:self-auto">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ $currentDate }} (WITA)</span>
            </div>
        </div>

        <!-- Grid Ringkasan Metrik Data Master -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm space-y-1">
                <p class="text-xs text-slate-500 font-medium">Tahun Ajaran Aktif</p>
                <p class="text-lg font-bold text-slate-900">
                    {{ $activeYear->name ?? 'Belum Diatur' }}
                </p>
                <p class="text-[11px] text-emerald-600 font-medium capitalize">
                    Semester {{ $activeYear->semester ?? '-' }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm space-y-1">
                <p class="text-xs text-slate-500 font-medium">Jumlah Guru</p>
                <p class="text-2xl font-bold text-slate-900">{{ $teacherCount }}</p>
                <p class="text-[11px] text-slate-400">Terdaftar di sistem</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm space-y-1">
                <p class="text-xs text-slate-500 font-medium">Jumlah Siswa</p>
                <p class="text-2xl font-bold text-slate-900">{{ $studentCount }}</p>
                <p class="text-[11px] text-slate-400">Total rombel aktif</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm space-y-1">
                <p class="text-xs text-slate-500 font-medium">Rombel / Kelas</p>
                <p class="text-2xl font-bold text-slate-900">{{ $classCount }}</p>
                <p class="text-[11px] text-slate-400">{{ $subjectCount }} Mata Pelajaran</p>
            </div>
        </div>

        <!-- Roadmap Pengerjaan Modul -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Modul Operasional Sistem</h3>
                    <p class="text-xs text-slate-500">Status perkembangan modul aplikasi Absensi & Jurnal SMK Yapparindo</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Fase 3A Selesai
                </span>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-emerald-700 uppercase">Fase 3A</span>
                        <span class="text-xs text-emerald-800 font-semibold">&#10003; Aktif</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Autentikasi & Otorisasi</h4>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Sistem login email/username, proteksi sesi, isolasi peran Guru vs Admin, dan layout responsif.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Fase 3B</span>
                        <span class="text-[11px] text-amber-700 font-medium">Langkah Berikutnya</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">Absensi Siswa & Jurnal Kelas</h4>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Pencatatan absensi per siswa (H/S/I/A/Training/DW), jurnal materi mengajar, dan rekap otomatis.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Fase 3C</span>
                        <span class="text-[11px] text-slate-400 font-medium">Masa Datang</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">PWA & Sinkronisasi Offline</h4>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        IndexedDB 1 minggu, service worker app shell, antrean sync idempotent, dan penanganan sinyal lemah.
                    </p>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

