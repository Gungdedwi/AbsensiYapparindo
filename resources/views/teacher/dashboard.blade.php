@extends('layouts.app')

@section('title', 'Ruang Guru')

@section('content')
<div class="min-h-full bg-slate-50 pb-12">
    <!-- Mobile-First App Bar Guru -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-base shadow-sm">
                    AY
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">Ruang Kerja Guru</h1>
                    <p class="text-[11px] text-slate-500">SMK Yapparindo</p>
                </div>
            </div>

            <!-- Tombol Keluar (Logout) -->
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
    </header>

    <!-- Konten Utama Guru (Mobile-First Container) -->
    <main class="max-w-2xl mx-auto px-4 pt-5 space-y-5">
        <!-- Kartu Sambutan & Profil Guru -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 text-white shadow-sm space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-500/40 text-[11px] font-medium tracking-wide uppercase">
                        Guru Pengajar
                    </span>
                    <h2 class="text-lg font-bold mt-1">{{ $teacher->name ?? $user->name }}</h2>
                    <p class="text-xs text-emerald-100">
                        NIP: {{ $teacher->nip ?? 'Belum tercatat' }}
                        @if($teacher && $teacher->status)
                            &bull; Status: <span class="capitalize">{{ $teacher->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Banner Tanggal WITA -->
            <div class="pt-3 border-t border-emerald-500/40 flex items-center justify-between text-xs text-emerald-100">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $currentDate }}</span>
                </span>
                <span class="font-mono text-[11px] bg-emerald-800/40 px-2 py-0.5 rounded">WITA</span>
            </div>
        </div>

        <!-- Notifikasi Status Fondasi Fase 3A -->
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Fase 3A: Fondasi Autentikasi & Ruang Kerja Aktif</span>
            </div>
            <p class="text-amber-800/90 pl-6 leading-relaxed">
                Anda berhasil masuk ke sistem dengan akun guru resmi. Fitur pencatatan absensi siswa per siswa dan jurnal materi akan hadir pada <strong>Fase 3B</strong>.
            </p>
        </div>

        <!-- Daftar Penugasan Mengajar Aktif -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Penugasan Mengajar Anda ({{ $assignments->count() }})
                </h3>
            </div>

            @forelse($assignments as $assignment)
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-emerald-300 transition space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-[11px] font-semibold text-slate-700">
                                {{ $assignment->classroom->name ?? 'Kelas' }}
                            </span>
                            <h4 class="text-sm font-bold text-slate-900 mt-1">
                                {{ $assignment->subject->name ?? 'Mata Pelajaran' }}
                            </h4>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ $assignment->academicYear->name ?? '' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 text-[11px]">Kode Mapel: {{ $assignment->subject->code ?? '-' }}</span>
                        <span class="text-emerald-700 font-medium text-[11px] bg-emerald-50 px-2 py-0.5 rounded-full">
                            Aktif
                        </span>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-slate-200 p-6 text-center text-xs text-slate-500 space-y-1">
                    <p class="font-medium">Belum ada penugasan mengajar yang terdaftar.</p>
                    <p class="text-[11px] text-slate-400">Silakan hubungi admin sekolah untuk pengaturan jadwal kelas Anda.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection

