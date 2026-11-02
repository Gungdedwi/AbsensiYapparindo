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

        <!-- Notifikasi Status Fondasi Fase 3C & PWA Offline -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Mode Offline PWA Aktif</h3>
                </div>
                <span id="pendingBadge" class="hidden px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">
                    0 Pending
                </span>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Jadwal & roster siswa dapat disimpan di memori perangkat (IndexedDB) untuk rentang 1 minggu ke depan. Absensi tetap dapat dicatat saat sinyal hilang.
            </p>

            <div class="pt-1 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    id="downloadBundleBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold border border-emerald-200 transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Roster 1 Minggu</span>
                </button>

                <button
                    type="button"
                    id="manualSyncBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Sinkronkan Sekarang</span>
                </button>
            </div>

            <div id="syncFeedback" class="text-[11px] text-slate-400 font-medium"></div>
        </div>

        <!-- Daftar Penugasan Mengajar Aktif -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Penugasan Mengajar Anda ({{ $assignments->count() }})
                </h3>
            </div>

            @forelse($assignments as $assignment)
                <a href="{{ route('teacher.assignments.show', $assignment->id) }}" class="block bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-emerald-500 hover:shadow-md transition space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded bg-emerald-100 text-[11px] font-bold text-emerald-800">
                                {{ $assignment->classroom->name ?? 'Kelas' }}
                            </span>
                            <h4 class="text-base font-bold text-slate-900 mt-1">
                                {{ $assignment->subject->name ?? 'Mata Pelajaran' }}
                            </h4>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ $assignment->academicYear->name ?? '' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 text-xs">Kode: <span class="font-mono">{{ $assignment->subject->code ?? '-' }}</span></span>
                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold text-xs">
                            <span>Buka Kelas</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const userId = window.currentUserId;
        const pendingBadge = document.getElementById('pendingBadge');
        const syncFeedback = document.getElementById('syncFeedback');
        const downloadBtn = document.getElementById('downloadBundleBtn');
        const syncBtn = document.getElementById('manualSyncBtn');

        async function refreshPendingCount() {
            if (!userId) return;
            try {
                const pending = await getPendingOutboxItems(userId);
                if (pending.length > 0) {
                    pendingBadge.textContent = pending.length + ' Pending Sync';
                    pendingBadge.classList.remove('hidden');
                } else {
                    pendingBadge.classList.add('hidden');
                }
            } catch (e) {
                console.error(e);
            }
        }

        if (downloadBtn) {
            downloadBtn.addEventListener('click', async function () {
                syncFeedback.textContent = 'Mengunduh roster dan jadwal 1 minggu...';
                try {
                    const res = await fetch('/guru/api/offline-data');
                    if (!res.ok) throw new Error('Gagal mengunduh paket data offline.');
                    const bundle = await res.json();
                    await saveOfflineBundle(userId, bundle);
                    syncFeedback.textContent = '✓ Data 1 minggu ke depan berhasil disimpan di perangkat (IndexedDB).';
                } catch (err) {
                    syncFeedback.textContent = '✕ Gagal mengunduh: ' + err.message;
                }
            });
        }

        if (syncBtn) {
            syncBtn.addEventListener('click', async function () {
                syncFeedback.textContent = 'Menyinkronkan data ke server...';
                const res = await syncOfflineQueue(userId);
                if (res.success) {
                    syncFeedback.textContent = '✓ ' + (res.count || 0) + ' data berhasil disinkronkan ke server.';
                    await refreshPendingCount();
                    if (res.count > 0) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    syncFeedback.textContent = '✕ ' + (res.message || res.error || 'Gagal sinkronisasi.');
                }
            });
        }

        await refreshPendingCount();
    });
</script>
@endsection

