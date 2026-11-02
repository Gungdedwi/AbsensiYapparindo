<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceMonitoringController extends Controller
{
    /**
     * Tampilkan pemantauan sesi pembelajaran dan presensi kelas harian.
     */
    public function index(Request $request): View
    {
        $todayWita = Carbon::now('Asia/Makassar')->toDateString();
        $selectedDate = $request->query('date', $todayWita);

        $sessions = LessonSession::whereDate('date', $selectedDate)
            ->with([
                'teachingAssignment.classroom',
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
                'journal.submitter',
                'attendances',
            ])
            ->latest('time_start')
            ->get();

        $formattedDate = Carbon::parse($selectedDate, 'Asia/Makassar')
            ->locale('id')
            ->translatedFormat('l, d F Y');

        return view('admin.attendance.index', [
            'sessions' => $sessions,
            'selectedDate' => $selectedDate,
            'formattedDate' => $formattedDate,
        ]);
    }

    /**
     * Tampilkan detail laporan absensi dan materi jurnal dari sesi kelas tertentu.
     */
    public function show(LessonSession $session): View
    {
        $session->load([
            'teachingAssignment.classroom',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'journal.submitter',
            'attendances.student' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('admin.attendance.show', [
            'session' => $session,
            'summary' => $session->attendance_summary,
        ]);
    }
}

