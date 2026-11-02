<?php

namespace Tests\Feature\Offline;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonJournal;
use App\Models\LessonSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\SyncLog;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
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

        $this->students = collect([
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1001',
                'name' => 'Ahmad Dahlan',
                'gender' => 'L',
                'is_active' => true,
            ]),
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1002',
                'name' => 'Bunga Citra',
                'gender' => 'P',
                'is_active' => true,
            ]),
        ]);
    }

    public function test_offline_fallback_page_can_be_rendered(): void
    {
        $response = $this->get('/offline');

        $response->assertStatus(200);
        $response->assertSee('Mode Luar Jaringan (Offline)');
        $response->assertSee('Sinyal Terputus');
    }

    public function test_teacher_can_download_offline_bundle_for_assigned_classes(): void
    {
        $response = $this->actingAs($this->teacherUser)->getJson('/guru/api/offline-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'teacher' => ['id', 'name', 'nip'],
            'range' => ['start', 'end', 'timezone'],
            'assignments',
            'synced_at',
        ]);

        $data = $response->json();
        $this->assertEquals($this->teacher->id, $data['teacher']['id']);
        $this->assertCount(1, $data['assignments']);
        $this->assertEquals('X TKJ 1', $data['assignments'][0]['classroom']['name']);
        $this->assertCount(2, $data['assignments'][0]['classroom']['students']);
    }

    public function test_idempotent_sync_endpoint_processes_offline_submission_successfully(): void
    {
        $idempotencyKey = 'ay-offline-test-uuid-001';

        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 2,
                    'topic' => 'Sinkronisasi Presensi Offline via PWA',
                    'notes' => 'Tercatat offline di lab tanpa sinyal',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'T', // Training
                    ],
                    'attendance_notes' => [
                        $this->students[1]->id => 'Pelatihan Industri',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'processed_count' => 1,
            'results' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                ],
            ],
        ]);

        // Verifikasi sesi tersimpan di database
        $session = LessonSession::where('teaching_assignment_id', $this->assignment->id)->first();
        $this->assertNotNull($session);
        $this->assertEquals('2026-11-03', $session->date->toDateString());
        $this->assertEquals('completed', $session->status);

        // Verifikasi log idempotensi tersimpan
        $syncLog = SyncLog::where('idempotency_key', $idempotencyKey)->first();
        $this->assertNotNull($syncLog);
        $this->assertEquals('synced', $syncLog->status);
        $this->assertEquals($session->id, $syncLog->lesson_session_id);

        // Verifikasi kehadiran siswa
        $this->assertDatabaseHas('student_attendance', [
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[0]->id,
            'status' => 'H',
        ]);
        $this->assertDatabaseHas('student_attendance', [
            'lesson_session_id' => $session->id,
            'student_id' => $this->students[1]->id,
            'status' => 'T',
            'notes' => 'Pelatihan Industri',
        ]);
    }

    public function test_idempotent_sync_rejects_duplicate_processing_for_same_idempotency_key(): void
    {
        $idempotencyKey = 'ay-offline-retry-uuid-002';

        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-04',
                    'time_start' => '10:00',
                    'time_end' => '11:30',
                    'period_start' => 3,
                    'period_end' => 4,
                    'meeting_number' => 3,
                    'topic' => 'Tes Idempotensi Retry',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];

        // Kiriman pertama
        $res1 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);
        $res1->assertStatus(200);
        $this->assertEquals(1, LessonSession::count());
        $this->assertEquals(2, StudentAttendance::count());

        // Kiriman kedua dengan idempotency_key yang sama (simulasi retry jaringan)
        $res2 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);
        $res2->assertStatus(200);
        $res2->assertJson([
            'results' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                ],
            ],
        ]);

        // Pastikan tidak ada data sesi atau absensi duplikat yang tercipta
        $this->assertEquals(1, LessonSession::count());
        $this->assertEquals(2, StudentAttendance::count());
    }

    public function test_server_wins_conflict_resolution_and_notification_generation(): void
    {
        $idempotencyKey = 'ay-offline-conflict-uuid-003';

        // 1. Admin telah membuat dan menyelesaikan sesi di server terlebih dahulu
        $adminSession = LessonSession::create([
            'teaching_assignment_id' => $this->assignment->id,
            'date' => '2026-11-05',
            'time_start' => '07:30',
            'time_end' => '09:00',
            'period_start' => 1,
            'period_end' => 2,
            'status' => 'completed',
        ]);
        LessonJournal::create([
            'lesson_session_id' => $adminSession->id,
            'meeting_number' => 1,
            'topic' => 'Materi Diinput Admin di Server',
            'submitted_at' => now(),
            'submitted_by' => 1,
        ]);
        StudentAttendance::create([
            'lesson_session_id' => $adminSession->id,
            'student_id' => $this->students[0]->id,
            'status' => 'H',
        ]);

        // 2. Guru mencoba mengirim data offline yang bertabrakan pada tanggal dan jam ke-1 yang sama
        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-05',
                    'time_start' => '07:30',
                    'time_end' => '09:00',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Materi Offline Guru Bertabrakan',
                    'attendance' => [
                        $this->students[0]->id => 'A', // Guru menandai Alpa offline
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'conflict',
                ],
            ],
        ]);

        // Verifikasi data server dipertahankan (server-wins: status siswa tetap H dari admin, bukan A)
        $adminAttendance = StudentAttendance::where('lesson_session_id', $adminSession->id)
            ->where('student_id', $this->students[0]->id)
            ->first();
        $this->assertEquals('H', $adminAttendance->status);

        // Verifikasi guru menerima notifikasi sistem di tabel notifications (D-025)
        $this->assertEquals(1, $this->teacherUser->notifications()->count());
        $notification = $this->teacherUser->notifications()->first();
        $data = json_decode($notification->data, true);
        $this->assertEquals('Konflik Sinkronisasi Absensi', $data['title']);
        $this->assertStringContainsString('telah dibuat/diubah oleh admin di server', $data['message']);
    }

    public function test_idor_protection_on_sync_endpoint(): void
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
            'items' => [
                [
                    'idempotency_key' => 'ay-idor-sync-004',
                    'teaching_assignment_id' => $this->assignment->id, // Penugasan milik Teacher A
                    'date' => '2026-11-06',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Percobaan Sync Ilegal',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                    ],
                ],
            ],
        ];

        // Guru lain mencoba melakukan sync terhadap penugasan Teacher A
        $response = $this->actingAs($otherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => 'ay-idor-sync-004',
                    'status' => 'failed',
                ],
            ],
        ]);

        $this->assertEquals(0, LessonSession::count());
    }
}

