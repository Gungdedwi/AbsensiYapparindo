@extends('layouts.app')

@section('title', 'Pemantauan Presensi Kelas & Jurnal Harian')

@section('content')
<div class="min-h-full bg-slate-50 pb-16">
    <!-- Header Admin -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Dashboard Admin</span>
                </a>
            </div>
            <span class="text-xs text-slate-500 font-medium">Monitoring Presensi Kelas</span>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 space-y-6">
        <!-- Filter Tanggal & Ringkasan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Pemantauan Presensi Mengajar Harian</h2>
                <p class="text-xs text-slate-500 mt-0.5">Sesi kelas dan jurnal yang telah difinalisasi oleh guru pengajar</p>
            </div>

            <!-- Form Filter Tanggal -->
            <form action="{{ route('admin.attendance.index') }}" method="GET" class="flex items-center gap-2">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition"
                >
                <button
                    type="submit"
                    class="px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition cursor-pointer"
                >
                    Tampilkan
                </button>
            </form>
        </div>

        <!-- Banner Info Tanggal -->
        <div class="flex items-center justify-between text-xs text-slate-500 px-1">
            <span>Menampilkan data untuk: <strong class="text-slate-800">{{ $formattedDate }} (WITA)</strong></span>
            <span class="font-mono">{{ $sessions->count() }} Sesi Tercatat</span>
        </div>

        <!-- Daftar Sesi Pembelajaran Harian -->
        <div class="space-y-3">
            @forelse($sessions as $session)
                @php $summary = $session->attendance_summary; @endphp
                <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-xs font-bold text-slate-800">
                                    {{ $session->teachingAssignment->classroom->name ?? 'Kelas' }}
                                </span>
                                <span class="text-xs font-bold text-slate-900">
                                    {{ $session->teachingAssignment->subject->name ?? 'Mata Pelajaran' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Guru: <strong>{{ $session->teachingAssignment->teacher->name ?? '-' }}</strong> &bull;
                                Pertemuan Ke-{{ $session->journal->meeting_number ?? '-' }} &bull;
                                Jam Ke-{{ $session->period_start }} s/d {{ $session->period_end }} ({{ substr($session->time_start, 0, 5) }} - {{ substr($session->time_end, 0, 5) }} WITA)
                            </p>
                        </div>

                        <a
                            href="{{ route('admin.sessions.show', $session->id) }}"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 self-start sm:self-auto bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200"
                        >
                            <span>Lihat Rincian Presensi</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <!-- Materi Pembelajaran -->
                    <div class="text-xs space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider text-[11px]">Materi:</span>
                        <p class="text-slate-800 font-medium leading-relaxed">{{ $session->journal->topic ?? '-' }}</p>
                    </div>

                    <!-- Breakdown Kehadiran Siswa -->
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-400 text-[11px]">Rekap:</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[11px]">Hadir: {{ $summary['H'] }}</span>
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[11px]">Sakit: {{ $summary['S'] }}</span>
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">Izin: {{ $summary['I'] }}</span>
                        <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[11px]">Alpa: {{ $summary['A'] }}</span>
                        @if($summary['T'] > 0)
                            <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-bold text-[11px]">Training: {{ $summary['T'] }}</span>
                        @endif
                        @if($summary['DW'] > 0)
                            <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold text-[11px]">DW: {{ $summary['DW'] }}</span>
                        @endif
                        <span class="text-slate-400 text-[11px] ml-auto">Total: {{ $summary['total'] }} Siswa</span>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-8 text-center text-xs text-slate-500 space-y-1">
                    <p class="font-medium text-sm text-slate-700">Belum ada sesi mengajar yang difinalisasi pada tanggal ini.</p>
                    <p class="text-slate-400">Pilih tanggal lain atau tunggu guru menyelesaikan pengisian absensi kelas.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection

