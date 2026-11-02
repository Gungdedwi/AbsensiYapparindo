<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LessonJournal;
use App\Models\LessonSession;
use App\Models\StudentAttendance;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    /**
     * Tampilkan rincian penugasan mengajar dan riwayat sesi.
     */
    public function assignment(TeachingAssignment $assignment): View
    {
        $this->authorizeTeacher($assignment);

        $assignment->load([
            'classroom.students' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'subject',
            'academicYear',
            'lessonSessions' => fn ($q) => $q->with('journal')->latest('date'),
        ]);

        return view('teacher.assignments.show', [
            'assignment' => $assignment,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan sesi mengajar dan absensi kelas.
     */
    public function create(TeachingAssignment $assignment): View
    {
        $this->authorizeTeacher($assignment);

        $students = $assignment->classroom->students()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $lastMeeting = $assignment->lessonSessions()
            ->with('journal')
            ->get()
            ->max(fn ($s) => $s->journal?->meeting_number) ?? 0;

        $nextMeetingNumber = $lastMeeting + 1;
        $nowWita = Carbon::now('Asia/Makassar');

        return view('teacher.sessions.create', [
            'assignment' => $assignment,
            'students' => $students,
            'nextMeetingNumber' => $nextMeetingNumber,
            'nowWita' => $nowWita,
        ]);
    }

    /**
     * Simpan sesi pembelajaran, jurnal mengajar, dan absensi siswa (Final Submitted).
     */
    public function store(Request $request, TeachingAssignment $assignment): RedirectResponse
    {
        $this->authorizeTeacher($assignment);

        $request->validate([
            'date' => ['required', 'date'],
            'time_start' => ['required', 'date_format:H:i'],
            'time_end' => ['required', 'date_format:H:i', 'after:time_start'],
            'period_start' => ['required', 'integer', 'min:1', 'max:16'],
            'period_end' => ['required', 'integer', 'min:'.$request->input('period_start', 1), 'max:16'],
            'meeting_number' => ['required', 'integer', 'min:1'],
            'topic' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:H,S,I,A,T,DW'],
            'attendance_notes' => ['nullable', 'array'],
            'attendance_notes.*' => ['nullable', 'string', 'max:255'],
        ], [
            'topic.required' => 'Materi/topik pembelajaran wajib diisi.',
            'attendance.required' => 'Catatan kehadiran siswa wajib diisi.',
            'time_end.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        $activeStudentIds = $assignment->classroom->students()
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $submittedStudentIds = array_map('intval', array_keys($request->input('attendance', [])));
        $missingStudentIds = array_diff($activeStudentIds, $submittedStudentIds);

        if (! empty($missingStudentIds)) {
            return back()->withErrors([
                'attendance' => 'Seluruh siswa aktif di kelas ini wajib memiliki status kehadiran.',
            ])->withInput();
        }

        $session = DB::transaction(function () use ($request, $assignment) {
            $session = LessonSession::create([
                'teaching_assignment_id' => $assignment->id,
                'date' => $request->input('date'),
                'time_start' => $request->input('time_start'),
                'time_end' => $request->input('time_end'),
                'period_start' => $request->input('period_start'),
                'period_end' => $request->input('period_end'),
                'status' => 'completed',
            ]);

            LessonJournal::create([
                'lesson_session_id' => $session->id,
                'meeting_number' => $request->input('meeting_number'),
                'topic' => $request->input('topic'),
                'notes' => $request->input('notes'),
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
            ]);

            $attendanceNotes = $request->input('attendance_notes', []);
            foreach ($request->input('attendance') as $studentId => $status) {
                StudentAttendance::create([
                    'lesson_session_id' => $session->id,
                    'student_id' => (int) $studentId,
                    'status' => $status,
                    'notes' => $attendanceNotes[$studentId] ?? null,
                ]);
            }

            return $session;
        });

        return redirect()->route('teacher.sessions.show', $session->id)
            ->with('status', 'Sesi pembelajaran dan absensi siswa berhasil difinalisasi.');
    }

    /**
     * Tampilkan pratinjau rincian sesi pembelajaran dan rekap absensi.
     */
    public function show(LessonSession $session): View
    {
        $this->authorizeTeacher($session->teachingAssignment);

        $session->load([
            'teachingAssignment.classroom',
            'teachingAssignment.subject',
            'teachingAssignment.academicYear',
            'journal.submitter',
            'attendances.student' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('teacher.sessions.show', [
            'session' => $session,
            'summary' => $session->attendance_summary,
        ]);
    }

    /**
     * Validasi kepemilikan penugasan mengajar untuk mencegah serangan IDOR.
     */
    private function authorizeTeacher(TeachingAssignment $assignment): void
    {
        $user = auth()->user();
        $teacher = $user?->teacher;

        if (! $teacher || $assignment->teacher_id !== $teacher->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk penugasan mengajar ini.');
        }
    }
}

