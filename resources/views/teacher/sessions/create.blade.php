@extends('layouts.app')

@section('title', 'Catat Sesi Pembelajaran & Absensi')

@section('content')
<div class="min-h-full bg-slate-50 pb-24">
    <!-- Header Mobile-First -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('teacher.assignments.show', $assignment->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Batal</span>
            </a>
            <span class="text-xs font-bold text-slate-800">
                {{ $assignment->classroom->name }} &bull; Pertemuan Ke-{{ $nextMeetingNumber }}
            </span>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 pt-5 space-y-6">
        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Harap Periksa Data Kembali</span>
                </div>
                @foreach ($errors->all() as $error)
                    <p class="pl-7 text-rose-700">&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('teacher.sessions.store', $assignment->id) }}" method="POST" id="attendanceForm" class="space-y-6">
            @csrf

            <!-- Bagian I: Data Waktu & Sesi Mengajar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-bold text-slate-900">1. Waktu & Sesi Pembelajaran</h2>
                    <p class="text-xs text-slate-500">{{ $assignment->subject->name }} &bull; {{ $assignment->classroom->name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 space-y-1">
                        <label for="date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Tanggal Sesi</label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            value="{{ old('date', $nowWita->toDateString()) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="time_start" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Jam Mulai</label>
                        <input
                            type="time"
                            name="time_start"
                            id="time_start"
                            value="{{ old('time_start', $nowWita->format('H:i')) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="time_end" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Jam Selesai</label>
                        <input
                            type="time"
                            name="time_end"
                            id="time_end"
                            value="{{ old('time_end', $nowWita->copy()->addMinutes(90)->format('H:i')) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="period_start" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Jam Ke-</label>
                        <input
                            type="number"
                            name="period_start"
                            id="period_start"
                            min="1"
                            max="16"
                            value="{{ old('period_start', 1) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="period_end" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Sampai Jam Ke-</label>
                        <input
                            type="number"
                            name="period_end"
                            id="period_end"
                            min="1"
                            max="16"
                            value="{{ old('period_end', 2) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Bagian II: Jurnal & Materi Pembelajaran -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-bold text-slate-900">2. Jurnal Mengajar</h2>
                    <p class="text-xs text-slate-500">Materi dan ringkasan pembelajaran kelas</p>
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <label for="meeting_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Pertemuan Ke-</label>
                        <input
                            type="number"
                            name="meeting_number"
                            id="meeting_number"
                            min="1"
                            value="{{ old('meeting_number', $nextMeetingNumber) }}"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="topic" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Materi / Topik Pembelajaran</label>
                        <input
                            type="text"
                            name="topic"
                            id="topic"
                            value="{{ old('topic') }}"
                            required
                            placeholder="Contoh: Pengenalan Sintaks Percabangan If-Else"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Catatan Kegiatan (Opsional)</label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="2"
                            placeholder="Catatan kendala, tugas yang diberikan, atau kegiatan khusus..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Bagian III: Presensi Siswa Individual -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">3. Presensi Siswa Individual</h2>
                        <p class="text-xs text-slate-500">Tandai status kehadiran setiap siswa ({{ $students->count() }} Siswa)</p>
                    </div>

                    <!-- Tombol Cepat: Tandai Semua Hadir -->
                    <button
                        type="button"
                        id="markAllPresentBtn"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition min-h-[38px] cursor-pointer self-start sm:self-auto"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tandai Semua Hadir</span>
                    </button>
                </div>

                <!-- Live Counter Bar Presensi -->
                <div id="liveSummaryBar" class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex flex-wrap items-center justify-between gap-2 text-xs font-medium">
                    <span class="text-slate-500">Rekap Sementara:</span>
                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold" id="countH">H: 0</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold" id="countS">S: 0</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold" id="countI">I: 0</span>
                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold" id="countA">A: 0</span>
                        <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold" id="countT">T: 0</span>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-bold" id="countDW">DW: 0</span>
                    </div>
                </div>

                <!-- Daftar Roster Siswa & Tombol Presensi -->
                <div class="divide-y divide-slate-100">
                    @foreach($students as $index => $student)
                        <div class="py-4 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-slate-400 font-mono text-xs w-4 text-right">{{ $index + 1 }}</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $student->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">NIS: {{ $student->nis ?? '-' }}</p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $student->gender }}
                                </span>
                            </div>

                            <!-- Pilihan Radio Status (H / S / I / A / T / DW) -->
                            <div class="grid grid-cols-6 gap-1.5 pt-1">
                                <!-- H (Hadir) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="H"
                                        class="peer sr-only status-radio"
                                        data-status="H"
                                        {{ old('attendance.'.$student->id) === 'H' ? 'checked' : '' }}
                                        required
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition min-h-[40px]">
                                        H
                                    </span>
                                </label>

                                <!-- S (Sakit) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="S"
                                        class="peer sr-only status-radio"
                                        data-status="S"
                                        {{ old('attendance.'.$student->id) === 'S' ? 'checked' : '' }}
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 transition min-h-[40px]">
                                        S
                                    </span>
                                </label>

                                <!-- I (Izin) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="I"
                                        class="peer sr-only status-radio"
                                        data-status="I"
                                        {{ old('attendance.'.$student->id) === 'I' ? 'checked' : '' }}
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition min-h-[40px]">
                                        I
                                    </span>
                                </label>

                                <!-- A (Alpa) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="A"
                                        class="peer sr-only status-radio"
                                        data-status="A"
                                        {{ old('attendance.'.$student->id) === 'A' ? 'checked' : '' }}
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition min-h-[40px]">
                                        A
                                    </span>
                                </label>

                                <!-- T (Training - Status Khusus Industri) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="T"
                                        class="peer sr-only status-radio"
                                        data-status="T"
                                        {{ old('attendance.'.$student->id) === 'T' ? 'checked' : '' }}
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 transition min-h-[40px]" title="Training Industri">
                                        T
                                    </span>
                                </label>

                                <!-- DW (Daily Worker - Status Khusus Magang Harian) -->
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="attendance[{{ $student->id }}]"
                                        value="DW"
                                        class="peer sr-only status-radio"
                                        data-status="DW"
                                        {{ old('attendance.'.$student->id) === 'DW' ? 'checked' : '' }}
                                    >
                                    <span class="flex items-center justify-center py-2 rounded-lg text-xs font-bold border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition min-h-[40px]" title="Daily Worker">
                                        DW
                                    </span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tombol Submit Final -->
            <div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur border-t border-slate-200 p-4 z-40">
                <div class="max-w-2xl mx-auto flex items-center gap-3">
                    <a
                        href="{{ route('teacher.assignments.show', $assignment->id) }}"
                        class="px-4 py-3 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition min-h-[44px] flex items-center justify-center"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-sm transition min-h-[44px] cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Kirim Laporan (Final Submitted)</span>
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const markAllBtn = document.getElementById('markAllPresentBtn');
        const radios = document.querySelectorAll('.status-radio');

        function updateSummary() {
            let counts = { H: 0, S: 0, I: 0, A: 0, T: 0, DW: 0 };
            radios.forEach(radio => {
                if (radio.checked) {
                    counts[radio.value] = (counts[radio.value] || 0) + 1;
                }
            });

            document.getElementById('countH').textContent = 'H: ' + counts.H;
            document.getElementById('countS').textContent = 'S: ' + counts.S;
            document.getElementById('countI').textContent = 'I: ' + counts.I;
            document.getElementById('countA').textContent = 'A: ' + counts.A;
            document.getElementById('countT').textContent = 'T: ' + counts.T;
            document.getElementById('countDW').textContent = 'DW: ' + counts.DW;
        }

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                const presentRadios = document.querySelectorAll('.status-radio[value="H"]');
                presentRadios.forEach(r => {
                    r.checked = true;
                });
                updateSummary();
            });
        }

        radios.forEach(r => {
            r.addEventListener('change', updateSummary);
        });

        updateSummary();

        // Tangani Pengiriman Form saat Offline
        const form = document.getElementById('attendanceForm');
        if (form) {
            form.addEventListener('submit', async function (e) {
                if (!navigator.onLine) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const attendance = {};
                    const attendanceNotes = {};

                    for (let [key, val] of formData.entries()) {
                        if (key.startsWith('attendance[')) {
                            const studentId = key.match(/\[(\d+)\]/)[1];
                            attendance[studentId] = val;
                        } else if (key.startsWith('attendance_notes[')) {
                            const studentId = key.match(/\[(\d+)\]/)[1];
                            attendanceNotes[studentId] = val;
                        }
                    }

                    const payload = {
                        teaching_assignment_id: {{ $assignment->id }},
                        date: formData.get('date'),
                        time_start: formData.get('time_start'),
                        time_end: formData.get('time_end'),
                        period_start: parseInt(formData.get('period_start')),
                        period_end: parseInt(formData.get('period_end')),
                        meeting_number: parseInt(formData.get('meeting_number')),
                        topic: formData.get('topic'),
                        notes: formData.get('notes'),
                        attendance: attendance,
                        attendance_notes: attendanceNotes
                    };

                    try {
                        await queueOfflineSession(window.currentUserId, payload);
                        alert('Mode Offline: Data absensi dan jurnal kelas telah disimpan di memori perangkat (IndexedDB). Sistem akan otomatis menyinkronkan data ini saat sinyal internet kembali.');
                        window.location.href = "{{ route('teacher.dashboard') }}";
                    } catch (err) {
                        alert('Gagal menyimpan ke penyimpanan lokal: ' + err.message);
                    }
                }
            });
        }
    });
</script>
@endsection

