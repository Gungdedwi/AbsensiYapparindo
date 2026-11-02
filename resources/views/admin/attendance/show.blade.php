@extends('layouts.app')

@section('title', 'Rincian Presensi Kelas — Admin')

@section('content')
<div class="min-h-full bg-slate-50 pb-16">
    <!-- Header Admin -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="{{ route('admin.attendance.index', ['date' => $session->date->toDateString()]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Pemantauan</span>
            </a>
            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                Final Submitted
            </span>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 space-y-6">
        <!-- Rincian Identitas Sesi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-bold">
                        {{ $session->teachingAssignment->classroom->name }} &bull; Pertemuan Ke-{{ $session->journal->meeting_number ?? '-' }}
                    </span>
                    <h2 class="text-xl font-bold text-slate-900 mt-1">
                        {{ $session->teachingAssignment->subject->name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Guru Pengajar: <strong>{{ $session->teachingAssignment->teacher->name }}</strong> &bull;
                        {{ $session->date->format('l, d F Y') }} &bull;
                        Jam Ke-{{ $session->period_start }}-{{ $session->period_end }} ({{ substr($session->time_start, 0, 5) }} - {{ substr($session->time_end, 0, 5) }} WITA)
                    </p>
                </div>
            </div>

            <!-- Jurnal Materi -->
            <div class="space-y-2 text-xs">
                <div>
                    <h3 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Materi / Topik:</h3>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $session->journal->topic }}</p>
                </div>
                @if($session->journal->notes)
                    <div class="pt-2 border-t border-slate-100">
                        <h3 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Catatan Tambahan:</h3>
                        <p class="text-slate-700 mt-0.5">{{ $session->journal->notes }}</p>
                    </div>
                @endif
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                    Diserahkan: {{ $session->journal->submitted_at ? $session->journal->submitted_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-' }} WITA
                    oleh {{ $session->journal->submitter->name ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Rekapitulasi Kehadiran Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Rekapitulasi Kehadiran Siswa (Total: {{ $summary['total'] }} Siswa)
            </h3>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 text-center">
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/60">
                    <p class="text-[11px] font-bold text-emerald-800">HADIR</p>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $summary['H'] }}</p>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/60">
                    <p class="text-[11px] font-bold text-amber-800">SAKIT</p>
                    <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $summary['S'] }}</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200/60">
                    <p class="text-[11px] font-bold text-blue-800">IZIN</p>
                    <p class="text-2xl font-black text-blue-700 mt-0.5">{{ $summary['I'] }}</p>
                </div>
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200/60">
                    <p class="text-[11px] font-bold text-rose-800">ALPA</p>
                    <p class="text-2xl font-black text-rose-700 mt-0.5">{{ $summary['A'] }}</p>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200/60">
                    <p class="text-[11px] font-bold text-purple-800">TRAINING</p>
                    <p class="text-2xl font-black text-purple-700 mt-0.5">{{ $summary['T'] }}</p>
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200/60">
                    <p class="text-[11px] font-bold text-indigo-800">DW</p>
                    <p class="text-2xl font-black text-indigo-700 mt-0.5">{{ $summary['DW'] }}</p>
                </div>
            </div>
        </div>

        <!-- Tabel Roster Presensi Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rincian Per Siswa</span>
                <span class="text-xs text-slate-500 font-mono">{{ $session->attendances->count() }} Siswa</span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($session->attendances as $index => $item)
                    <div class="p-3.5 sm:px-6 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-mono text-xs w-5 text-right">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item->student->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">NIS: {{ $item->student->nis ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($item->status === 'H')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800">
                                    Hadir
                                </span>
                            @elseif($item->status === 'S')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800">
                                    Sakit
                                </span>
                            @elseif($item->status === 'I')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-800">
                                    Izin
                                </span>
                            @elseif($item->status === 'A')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800">
                                    Alpa
                                </span>
                            @elseif($item->status === 'T')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-purple-100 text-purple-800" title="Training Industri">
                                    Training
                                </span>
                            @elseif($item->status === 'DW')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800" title="Daily Worker">
                                    DW
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>
</div>
@endsection

