<?php

namespace Tests\Feature\Attendance;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonJournal;
use App\Models\LessonSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceAndJournalTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private Classroom $classroom;
    private Subject $subject;
    private AcademicYear $academicYear;
    private TeachingAssignment $assignment;
    /** @var \Illuminate\Database\Eloquent\Collection<int, Student> */
    private $students;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        $this->teacherUser = User::factory()->create([
            'name' => 'Budi Santoso, S.Kom.',
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198501012010011001',
            'name' => $this->teacherUser->name,
            'gender' => 'L',
            'phone' => '081234567890',
            'status' => 'tetap',
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'X TKJ 1',
            'grade_level' => 'X',
            'major' => 'Teknik Komputer dan Jaringan',
        ]);

        $this->subject = Subject::create([
            'code' => 'TKJ-01',
            'name' => 'Dasar Pemrograman',
            'is_active' => true,
        ]);

        $this->assignment = TeachingAssignment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        // Buat 3 siswa aktif
        $this->students = collect([
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1001',
                'nisn' => '0081234561',
                'name' => 'Ahmad Dahlan',
                'gender' => 'L',
                'is_active' => true,
            ]),
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1002',
                'nisn' => '0081234562',
                'name' => 'Bunga Citra',
                'gender' => 'P',
                'is_active' => true,
            ]),
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1003',
                'nisn' => '0081234563',
                'name' => 'Candra Wijaya',
                'gender' => 'L',
                'is_active' => true,
            ]),
        ]);
    }

    public function test_teacher_can_view_assignment_details_and_active_students(): void
    {
        $response = $this->actingAs($this->teacherUser)->get("/guru/penugasan/{$this->assignment->id}");

        $response->assertStatus(200);
        $response->assertSee('X TKJ 1');
        $response->assertSee('Dasar Pemrograman');
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('Mulai Sesi Pembelajaran Baru');
    }

    public function test_teacher_is_forbidden_from_viewing_another_teachers_assignment(): void
    {
        $otherUser = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        Teacher::create([
            'user_id' => $otherUser->id,
            'name' => 'Guru Lain',
            'gender' => 'L',
            'status' => 'tetap',
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherUser)->get("/guru/penugasan/{$this->assignment->id}");

        $response->assertStatus(403);
    }

    public function test_teacher_can_view_session_creation_form(): void
    {
        $response = $this->actingAs($this->teacherUser)->get("/guru/penugasan/{$this->assignment->id}/sesi/buat");

        $response->assertStatus(200);
        $response->assertSee('Pertemuan Ke-');
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('Tandai Semua Hadir');
        $response->assertSee('Kirim Laporan (Final Submitted)');
    }

    public function test_teacher_can_submit_session_journal_and_student_attendance_successfully(): void
    {
        $payload = [
            'date' => '2026-11-02',
            'time_start' => '08:00',
            'time_end' => '09:30',
            'period_start' => 1,
            'period_end' => 2,
            'meeting_number' => 1,
            'topic' => 'Pengenalan Bahasa PHP dan Struktur Kontrol',
            'notes' => 'Praktik lab komputer berjalan lancar',
            'attendance' => [
                $this->students[0]->id => 'H',  // Hadir
                $this->students[1]->id => 'S',  // Sakit
                $this->students[2]->id => 'T',  // Training (Status Khusus)
            ],
            'attendance_notes' => [
                $this->students[1]->id => 'Surat dokter terlampir',
                $this->students[2]->id => 'Pelatihan PT Telkom',
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->post("/guru/penugasan/{$this->assignment->id}/sesi", $payload);

        $session = LessonSession::first();
        $this->assertNotNull($session);
        $response->assertRedirect("/guru/sesi/{$session->id}");

        // Verifikasi sesi tersimpan dengan status completed
        $this->assertEquals('completed', $session->status);
        $this->assertEquals('2026-11-02', $session->date->toDateString());

        // Verifikasi jurnal mengajar
        $journal = LessonJournal::where('lesson_session_id', $session->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('Pengenalan Bahasa PHP dan Struktur Kontrol', $journal->topic);
        $this->assertEquals($this->teacherUser->id, $journal->submitted_by);
        $this->assertNotNull($journal->submitted_at);

        // Verifikasi catatan kehadiran tiap siswa
        $this->assertDatabaseHas('student_attendance', [
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[0]->id,
            'status' => 'H',
        ]);
        $this->assertDatabaseHas('student_attendance', [
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[1]->id,
            'status' => 'S',
            'notes' => 'Surat dokter terlampir',
        ]);
        $this->assertDatabaseHas('student_attendance', [
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[2]->id,
            'status' => 'T',
            'notes' => 'Pelatihan PT Telkom',
        ]);

        // Verifikasi rekap otomatis
        $summary = $session->attendance_summary;
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['H']);
        $this->assertEquals(1, $summary['S']);
        $this->assertEquals(1, $summary['T']);
        $this->assertEquals(0, $summary['A']);
    }

    public function test_submission_is_rejected_if_any_active_student_attendance_is_missing(): void
    {
        // Hanya mengisi 2 dari 3 siswa aktif
        $payload = [
            'date' => '2026-11-02',
            'time_start' => '08:00',
            'time_end' => '09:30',
            'period_start' => 1,
            'period_end' => 2,
            'meeting_number' => 1,
            'topic' => 'Materi Uji',
            'attendance' => [
                $this->students[0]->id => 'H',
                $this->students[1]->id => 'H',
                // Siswa 2 dilewati
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->post("/guru/penugasan/{$this->assignment->id}/sesi", $payload);

        $response->assertSessionHasErrors('attendance');
        $this->assertEquals(0, LessonSession::count());
        $this->assertEquals(0, StudentAttendance::count());
    }

    public function test_teacher_cannot_submit_session_for_another_teachers_assignment(): void
    {
        $otherUser = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        Teacher::create([
            'user_id' => $otherUser->id,
            'name' => 'Guru Lain',
            'gender' => 'L',
            'status' => 'tetap',
            'is_active' => true,
        ]);

        $payload = [
            'date' => '2026-11-02',
            'time_start' => '08:00',
            'time_end' => '09:30',
            'period_start' => 1,
            'period_end' => 2,
            'meeting_number' => 1,
            'topic' => 'Mencoba Bypass IDOR',
            'attendance' => [
                $this->students[0]->id => 'H',
                $this->students[1]->id => 'H',
                $this->students[2]->id => 'H',
            ],
        ];

        $response = $this->actingAs($otherUser)->post("/guru/penugasan/{$this->assignment->id}/sesi", $payload);

        $response->assertStatus(403);
        $this->assertEquals(0, LessonSession::count());
    }

    public function test_admin_can_view_daily_attendance_monitoring_and_session_details(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Buat 1 sesi selesai
        $session = LessonSession::create([
            'teaching_assignment_id' => $this->assignment->id,
            'date' => '2026-11-02',
            'time_start' => '08:00',
            'time_end' => '09:30',
            'period_start' => 1,
            'period_end' => 2,
            'status' => 'completed',
        ]);
        LessonJournal::create([
            'lesson_session_id' => $session->id,
            'meeting_number' => 1,
            'topic' => 'Materi Terpantau Admin',
            'submitted_at' => now(),
            'submitted_by' => $this->teacherUser->id,
        ]);
        StudentAttendance::create([
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[0]->id,
            'status' => 'DW',
        ]);

        // Admin melihat daftar sesi harian
        $responseIndex = $this->actingAs($adminUser)->get('/admin/absensi?date=2026-11-02');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Materi Terpantau Admin');
        $responseIndex->assertSee('X TKJ 1');

        // Admin melihat detail presensi sesi
        $responseShow = $this->actingAs($adminUser)->get("/admin/sesi/{$session->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Ahmad Dahlan');
        $responseShow->assertSee('Daily Worker');
    }

    public function test_teacher_is_forbidden_from_viewing_admin_attendance_monitoring(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/admin/absensi');

        $response->assertStatus(403);
    }
}

