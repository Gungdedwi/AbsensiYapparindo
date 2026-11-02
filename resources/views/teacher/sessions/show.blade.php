@extends('layouts.app')

@section('title', 'Rincian Sesi Pembelajaran & Absensi')

@section('content')
<div class="min-h-full bg-slate-50 pb-16">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('teacher.assignments.show', $session->teaching_assignment_id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Penugasan</span>
            </a>
            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                Final Submitted
            </span>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 pt-5 space-y-5">
        <!-- Flash Message Status -->
        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2.5 font-medium">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Kartu Identitas Sesi & Jurnal Mengajar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                        Pertemuan Ke-{{ $session->journal->meeting_number ?? '-' }}
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">
                        {{ $session->teachingAssignment->classroom->name }} &bull; {{ $session->teachingAssignment->subject->name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $session->date->format('l, d F Y') }} (WITA) &bull; Jam Ke-{{ $session->period_start }} s/d {{ $session->period_end }} ({{ substr($session->time_start, 0, 5) }} - {{ substr($session->time_end, 0, 5) }})
                    </p>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <h3 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Materi / Topik Pembelajaran:</h3>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5 leading-relaxed">
                        {{ $session->journal->topic }}
                    </p>
                </div>

                @if($session->journal->notes)
                    <div class="pt-2 border-t border-slate-100">
                        <h3 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Catatan Kegiatan:</h3>
                        <p class="text-slate-700 mt-0.5 leading-relaxed">{{ $session->journal->notes }}</p>
                    </div>
                @endif

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Diserahkan oleh: <strong>{{ $session->journal->submitter->name ?? 'Guru Pengajar' }}</strong></span>
                    <span>Waktu: {{ $session->journal->submitted_at ? $session->journal->submitted_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-' }} WITA</span>
                </div>
            </div>
        </div>

        <!-- Rekapitulasi Kehadiran Kelas -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Rekapitulasi Kehadiran Siswa (Total: {{ $summary['total'] }} Siswa)
            </h3>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-center">
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200/60">
                    <p class="text-[11px] font-bold text-emerald-800">HADIR</p>
                    <p class="text-xl font-black text-emerald-700 mt-0.5">{{ $summary['H'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200/60">
                    <p class="text-[11px] font-bold text-amber-800">SAKIT</p>
                    <p class="text-xl font-black text-amber-700 mt-0.5">{{ $summary['S'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200/60">
                    <p class="text-[11px] font-bold text-blue-800">IZIN</p>
                    <p class="text-xl font-black text-blue-700 mt-0.5">{{ $summary['I'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200/60">
                    <p class="text-[11px] font-bold text-rose-800">ALPA</p>
                    <p class="text-xl font-black text-rose-700 mt-0.5">{{ $summary['A'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200/60">
                    <p class="text-[11px] font-bold text-purple-800">TRAINING</p>
                    <p class="text-xl font-black text-purple-700 mt-0.5">{{ $summary['T'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200/60">
                    <p class="text-[11px] font-bold text-indigo-800">DW</p>
                    <p class="text-xl font-black text-indigo-700 mt-0.5">{{ $summary['DW'] }}</p>
                </div>
            </div>
        </div>

        <!-- Daftar Presensi Per Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Roster Presensi Siswa</span>
                <span class="text-xs text-slate-500 font-mono">{{ $session->attendances->count() }} Data</span>
            </div>

            @foreach($session->attendances as $index => $item)
                <div class="p-3.5 sm:px-4 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-slate-400 font-mono text-[11px] w-4 text-right">{{ $index + 1 }}</span>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $item->student->name }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">NIS: {{ $item->student->nis ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($item->status === 'H')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800">
                                Hadir (H)
                            </span>
                        @elseif($item->status === 'S')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800">
                                Sakit (S)
                            </span>
                        @elseif($item->status === 'I')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-800">
                                Izin (I)
                            </span>
                        @elseif($item->status === 'A')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800">
                                Alpa (A)
                            </span>
                        @elseif($item->status === 'T')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-100 text-purple-800" title="Training Industri">
                                Training (T)
                            </span>
                        @elseif($item->status === 'DW')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800" title="Daily Worker">
                                DW
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </main>
</div>
@endsection

