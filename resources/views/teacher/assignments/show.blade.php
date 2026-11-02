@extends('layouts.app')

@section('title', ($assignment->classroom->name ?? 'Kelas') . ' — ' . ($assignment->subject->name ?? 'Mapel'))

@section('content')
<div class="min-h-full bg-slate-50 pb-16">
    <!-- Header Guru -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
            <span class="text-xs font-mono text-slate-400">{{ $assignment->academicYear->name ?? '' }}</span>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 pt-5 space-y-5">
        <!-- Kartu Identitas Penugasan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                        {{ $assignment->classroom->name ?? 'Kelas' }} &bull; {{ $assignment->classroom->major ?? '' }}
                    </span>
                    <h2 class="text-xl font-bold text-slate-900 mt-1.5">{{ $assignment->subject->name ?? 'Mata Pelajaran' }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kode: {{ $assignment->subject->code ?? '-' }} &bull; Semester {{ $assignment->academicYear->semester ?? '-' }}</p>
                </div>
            </div>

            <!-- Tombol Aksi Utama -->
            <div class="pt-2">
                <a
                    href="{{ route('teacher.sessions.create', $assignment->id) }}"
                    class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-sm transition min-h-[44px]"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Mulai Sesi Pembelajaran Baru</span>
                </a>
            </div>
        </div>

        <!-- Riwayat Sesi Pembelajaran -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Riwayat Sesi Pembelajaran ({{ $assignment->lessonSessions->count() }})
            </h3>

            @forelse($assignment->lessonSessions as $session)
                <a href="{{ route('teacher.sessions.show', $session->id) }}" class="block bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-emerald-400 transition space-y-2.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-[11px] font-bold text-slate-700">
                                Pertemuan Ke-{{ $session->journal->meeting_number ?? '-' }}
                            </span>
                            <h4 class="text-sm font-bold text-slate-900 mt-1">
                                {{ $session->journal->topic ?? 'Materi belum tercatat' }}
                            </h4>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">
                            {{ $session->date->format('d/m/Y') }}
                        </span>
                    </div>

                    <!-- Ringkasan Rekap Kehadiran Siswa -->
                    @php $summary = $session->attendance_summary; @endphp
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                            Hadir: {{ $summary['H'] }}
                        </span>
                        @if($summary['S'] > 0)
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">
                                Sakit: {{ $summary['S'] }}
                            </span>
                        @endif
                        @if($summary['I'] > 0)
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">
                                Izin: {{ $summary['I'] }}
                            </span>
                        @endif
                        @if($summary['A'] > 0)
                            <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold">
                                Alpa: {{ $summary['A'] }}
                            </span>
                        @endif
                        @if($summary['T'] > 0)
                            <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-medium">
                                Training: {{ $summary['T'] }}
                            </span>
                        @endif
                        @if($summary['DW'] > 0)
                            <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-medium">
                                DW: {{ $summary['DW'] }}
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-slate-200 p-6 text-center text-xs text-slate-500 space-y-1">
                    <p class="font-medium">Belum ada sesi pembelajaran yang tercatat.</p>
                    <p class="text-[11px] text-slate-400">Tekan tombol "Mulai Sesi Pembelajaran Baru" di atas untuk mengisi absensi kelas hari ini.</p>
                </div>
            @endforelse
        </div>

        <!-- Daftar Roster Siswa Aktif -->
        <div class="space-y-3 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Daftar Siswa Kelas {{ $assignment->classroom->name }} ({{ $assignment->classroom->students->count() }})
            </h3>

            <div class="bg-white rounded-xl border border-slate-200/80 divide-y divide-slate-100 overflow-hidden shadow-sm">
                @forelse($assignment->classroom->students as $idx => $student)
                    <div class="p-3 sm:px-4 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-mono text-[11px] w-5 text-right">{{ $idx + 1 }}</span>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $student->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">NIS: {{ $student->nis ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $student->gender }}
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400">
                        Tidak ada siswa aktif terdaftar di rombel ini.
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</div>
@endsection

