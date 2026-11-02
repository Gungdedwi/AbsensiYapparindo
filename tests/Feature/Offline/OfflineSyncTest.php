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
                'nisn' => '0012345678',
                'name' => 'Ahmad Dahlan',
                'gender' => 'L',
                'is_active' => true,
            ]),
            Student::create([
                'class_id' => $this->classroom->id,
                'nis' => '1002',
                'nisn' => '0012345679',
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

    /**
     * 1. IDOR siswa: guru A mengirim item sinkronisasi dengan student_id milik kelas lain
     * -> item ditolak dan tidak ada StudentAttendance yang tertulis untuk siswa itu.
     */
    public function test_idor_student_from_another_class_is_rejected(): void
    {
        $otherClass = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'XI RPL 1',
            'grade_level' => 'XI',
            'major' => 'Rekayasa Perangkat Lunak',
        ]);

        $outsideStudent = Student::create([
            'class_id' => $otherClass->id,
            'nis' => '9999',
            'name' => 'Siswa Luar Kelas',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'idempotency_key' => 'ay-idor-student-001',
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Percobaan IDOR Siswa',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                        $outsideStudent->id => 'H', // Siswa dari kelas lain!
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => 'ay-idor-student-001',
                    'status' => 'rejected',
                    'message' => 'Terdapat siswa yang bukan anggota aktif di kelas penugasan ini.',
                ],
            ],
        ]);

        $this->assertEquals(0, LessonSession::count());
        $this->assertEquals(0, StudentAttendance::where('student_id', $outsideStudent->id)->count());
    }

    /**
     * 2. IDOR penugasan: guru A mengirim teaching_assignment_id milik guru B -> ditolak.
     */
    public function test_idor_teaching_assignment_belonging_to_another_teacher_is_rejected(): void
    {
        $otherUser = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $otherTeacher = Teacher::create([
            'user_id' => $otherUser->id,
            'name' => 'Guru Lain',
            'gender' => 'L',
            'status' => 'tetap',
            'is_active' => true,
        ]);

        $otherAssignment = TeachingAssignment::create([
            'teacher_id' => $otherTeacher->id,
            'class_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'idempotency_key' => 'ay-idor-assignment-002',
                    'teaching_assignment_id' => $otherAssignment->id, // Milik Guru Lain
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Percobaan IDOR Penugasan',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];

        // Guru A mencoba mengirim sinkronisasi untuk penugasan milik Guru Lain
        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => 'ay-idor-assignment-002',
                    'status' => 'rejected',
                    'message' => 'Akses ditolak. Penugasan bukan milik guru yang sedang login.',
                ],
            ],
        ]);

        $this->assertEquals(0, LessonSession::count());
    }

    /**
     * 3. Siswa aktif kelas yang tidak disertakan di attendance -> ditolak.
     */
    public function test_missing_active_student_in_attendance_is_rejected(): void
    {
        $payload = [
            'items' => [
                [
                    'idempotency_key' => 'ay-missing-student-003',
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Tes Kelengkapan Siswa Aktif',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        // $this->students[1] sengaja dihilangkan
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => 'ay-missing-student-003',
                    'status' => 'rejected',
                    'message' => 'Seluruh siswa aktif di kelas ini wajib memiliki status kehadiran.',
                ],
            ],
        ]);

        $this->assertEquals(0, LessonSession::count());
    }

    /**
     * 4. Status kehadiran tidak valid (misalnya "X") -> ditolak dengan pesan field yang jelas.
     */
    public function test_invalid_attendance_status_is_rejected_with_field_errors(): void
    {
        $payload = [
            'items' => [
                [
                    'idempotency_key' => 'ay-invalid-status-004',
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Status Kehadiran Tidak Sah',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'X', // Tidak valid!
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'results' => [
                [
                    'idempotency_key' => 'ay-invalid-status-004',
                    'status' => 'rejected',
                    'message' => 'Validasi data gagal.',
                ],
            ],
        ]);

        $results = $response->json('results');
        $this->assertArrayHasKey('attendance.'.$this->students[1]->id, $results[0]['errors']);
        $this->assertEquals(0, LessonSession::count());
    }

    /**
     * 5. Retry setelah gagal: simulasikan kegagalan sementara pada item,
     * lalu kirim ulang dengan idempotency_key yang sama -> berhasil diproses dan tidak tertahan di status failed.
     */
    public function test_retry_after_failed_or_rejected_item_succeeds_and_updates_log(): void
    {
        $idempotencyKey = 'ay-retry-after-fail-005';

        // Langkah 1: Kiriman pertama gagal validasi (jam selesai lebih awal dari jam mulai)
        $invalidPayload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '10:00',
                    'time_end' => '09:00', // Salah: time_end < time_start
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Topik Awal',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];

        $res1 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $invalidPayload);
        $res1->assertStatus(200);
        $this->assertEquals('rejected', $res1->json('results.0.status'));
        $this->assertEquals(1, SyncLog::where('user_id', $this->teacherUser->id)->where('idempotency_key', $idempotencyKey)->count());
        $this->assertEquals(0, LessonSession::count());

        // Langkah 2: Klien memperbaiki payload dan mengirim ulang dengan idempotency_key yang sama persis
        $validPayload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-03',
                    'time_start' => '08:00',
                    'time_end' => '09:30', // Sudah diperbaiki
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Topik Setelah Perbaikan',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];

        $res2 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $validPayload);
        $res2->assertStatus(200);
        $res2->assertJson([
            'results' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                ],
            ],
        ]);

        // Verifikasi log diperbarui, bukan membuat baris ganda
        $logs = SyncLog::where('user_id', $this->teacherUser->id)->where('idempotency_key', $idempotencyKey)->get();
        $this->assertCount(1, $logs);
        $this->assertEquals('synced', $logs->first()->status);
        $this->assertEquals(1, LessonSession::count());
    }

    /**
     * 6. Idempotensi: kirim item yang sama dua kali -> hanya satu LessonSession terbentuk,
     * respons kedua mengembalikan hasil yang sama.
     */
    public function test_idempotent_duplicate_submission_creates_only_one_session_and_returns_same_result(): void
    {
        $idempotencyKey = 'ay-idempotent-dup-006';

        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-04',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Materi Idempotensi',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'I',
                    ],
                ],
            ],
        ];

        $res1 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);
        $res1->assertStatus(200);
        $sessionId = $res1->json('results.0.session_id');
        $this->assertNotNull($sessionId);
        $this->assertEquals(1, LessonSession::count());

        $res2 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);
        $res2->assertStatus(200);
        $res2->assertJson([
            'results' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                    'session_id' => $sessionId,
                ],
            ],
        ]);

        $this->assertEquals(1, LessonSession::count());
        $this->assertEquals(2, StudentAttendance::count());
    }

    /**
     * 7. Key yang sama dari dua user berbeda tidak saling membocorkan status atau session_id.
     */
    public function test_same_idempotency_key_from_two_different_users_does_not_leak_session_or_status(): void
    {
        $sharedKey = 'ay-shared-uuid-007';

        // Guru 2 beserta penugasan & kelasnya sendiri
        $teacherUser2 = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $teacher2 = Teacher::create([
            'user_id' => $teacherUser2->id,
            'name' => 'Guru Dua, S.Pd.',
            'gender' => 'P',
            'status' => 'tetap',
            'is_active' => true,
        ]);
        $classroom2 = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'X TKJ 2',
            'grade_level' => 'X',
            'major' => 'Teknik Komputer dan Jaringan',
        ]);
        $studentClass2 = Student::create([
            'class_id' => $classroom2->id,
            'nis' => '2001',
            'name' => 'Siswa Kelas Dua',
            'gender' => 'L',
            'is_active' => true,
        ]);
        $assignment2 = TeachingAssignment::create([
            'teacher_id' => $teacher2->id,
            'class_id' => $classroom2->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        // Guru 1 melakukan sync dengan $sharedKey
        $payload1 = [
            'items' => [
                [
                    'idempotency_key' => $sharedKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-04',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Sesi Guru 1',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];
        $res1 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload1);
        $res1->assertStatus(200);
        $session1Id = $res1->json('results.0.session_id');

        // Guru 2 melakukan sync dengan $sharedKey yang sama
        $payload2 = [
            'items' => [
                [
                    'idempotency_key' => $sharedKey,
                    'teaching_assignment_id' => $assignment2->id,
                    'date' => '2026-11-04',
                    'time_start' => '10:00',
                    'time_end' => '11:30',
                    'period_start' => 3,
                    'period_end' => 4,
                    'meeting_number' => 1,
                    'topic' => 'Sesi Guru 2',
                    'attendance' => [
                        $studentClass2->id => 'H',
                    ],
                ],
            ],
        ];
        $res2 = $this->actingAs($teacherUser2)->postJson('/guru/api/sync', $payload2);
        $res2->assertStatus(200);
        $session2Id = $res2->json('results.0.session_id');

        // Pastikan Guru 2 mendapatkan sesi miliknya sendiri dan bukan membocorkan sesi milik Guru 1
        $this->assertNotEquals($session1Id, $session2Id);
        $this->assertEquals(2, LessonSession::count());
        $this->assertEquals(2, SyncLog::where('idempotency_key', $sharedKey)->count());
    }

    /**
     * 8. Dua request bersamaan dengan key yang sama tidak menghasilkan error 500.
     */
    public function test_concurrent_or_immediate_duplicate_requests_do_not_produce_500_error(): void
    {
        $idempotencyKey = 'ay-concurrent-test-008';

        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-05',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Simulasi Konkurensi',
                    'attendance' => [
                        $this->students[0]->id => 'H',
                        $this->students[1]->id => 'H',
                    ],
                ],
            ],
        ];

        // Simulasi 2 pengiriman berurutan cepat / idempotent
        $res1 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);
        $res2 = $this->actingAs($this->teacherUser)->postJson('/guru/api/sync', $payload);

        $res1->assertStatus(200);
        $res2->assertStatus(200);
        $this->assertEquals(1, LessonSession::count());
    }

    /**
     * 9. Konflik: sesi sudah ada di server -> status conflict, data server tidak berubah,
     * pesan tidak menyebut "admin" secara salah.
     */
    public function test_conflict_server_wins_preserves_server_data_and_message_does_not_falsely_say_admin(): void
    {
        $idempotencyKey = 'ay-conflict-test-009';

        $submitterUser = User::factory()->create([
            'name' => 'Wali Kelas Handoko, S.Pd.',
            'role' => 'teacher',
        ]);

        // Sesi sudah dibuat di server oleh Pak Handoko
        $serverSession = LessonSession::create([
            'teaching_assignment_id' => $this->assignment->id,
            'date' => '2026-11-06',
            'time_start' => '08:00',
            'time_end' => '09:30',
            'period_start' => 1,
            'period_end' => 2,
            'status' => 'completed',
        ]);

        LessonJournal::create([
            'lesson_session_id' => $serverSession->id,
            'meeting_number' => 1,
            'topic' => 'Materi Diinput Handoko di Server',
            'submitted_at' => now(),
            'submitted_by' => $submitterUser->id,
        ]);

        StudentAttendance::create([
            'lesson_session_id' => $serverSession->id,
            'student_id' => $this->students[0]->id,
            'status' => 'H',
        ]);
        StudentAttendance::create([
            'lesson_session_id' => $serverSession->id,
            'student_id' => $this->students[1]->id,
            'status' => 'H',
        ]);

        // Guru offline mengirim sesi yang menabrak tanggal & jam yang sama, tapi menandai alpa
        $payload = [
            'items' => [
                [
                    'idempotency_key' => $idempotencyKey,
                    'teaching_assignment_id' => $this->assignment->id,
                    'date' => '2026-11-06',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'period_start' => 1,
                    'period_end' => 2,
                    'meeting_number' => 1,
                    'topic' => 'Materi Offline Menabrak',
                    'attendance' => [
                        $this->students[0]->id => 'A',
                        $this->students[1]->id => 'A',
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
                    'session_id' => $serverSession->id,
                ],
            ],
        ]);

        // Verifikasi pesan: tidak menyebut "admin" sembarangan, menyebut nama pembuat server
        $resultMessage = $response->json('results.0.message');
        $this->assertStringContainsString('sudah ada di server oleh Wali Kelas Handoko, S.Pd.', $resultMessage);
        $this->assertStringNotContainsString('oleh admin', $resultMessage);

        // Verifikasi data server tidak berubah (server-wins: tetap Hadir 'H')
        $attendance0 = StudentAttendance::where('lesson_session_id', $serverSession->id)
            ->where('student_id', $this->students[0]->id)
            ->first();
        $this->assertEquals('H', $attendance0->status);

        // Verifikasi notifikasi tersimpan
        $notification = $this->teacherUser->notifications()->first();
        $this->assertNotNull($notification);
        $notifData = json_decode($notification->data, true);
        $this->assertStringContainsString('sudah ada di server oleh Wali Kelas Handoko, S.Pd.', $notifData['message']);
    }

    /**
     * 10. Paket offline hanya berisi id dan name siswa, tanpa nisn, nis, dan nip.
     */
    public function test_offline_bundle_contains_only_minimum_data_without_nisn_nis_and_nip(): void
    {
        $response = $this->actingAs($this->teacherUser)->getJson('/guru/api/offline-data');

        $response->assertStatus(200);
        $data = $response->json();

        // 1. Data guru: hanya id dan name, TIDAK ADA nip
        $this->assertArrayHasKey('id', $data['teacher']);
        $this->assertArrayHasKey('name', $data['teacher']);
        $this->assertArrayNotHasKey('nip', $data['teacher']);

        // 2. Data penugasan dan siswa: siswa hanya memiliki id dan name, TIDAK ADA nis, nisn, gender
        $this->assertNotEmpty($data['assignments']);
        $studentsList = $data['assignments'][0]['classroom']['students'];
        $this->assertCount(2, $studentsList);

        foreach ($studentsList as $studentItem) {
            $this->assertArrayHasKey('id', $studentItem);
            $this->assertArrayHasKey('name', $studentItem);
            $this->assertArrayNotHasKey('nis', $studentItem);
            $this->assertArrayNotHasKey('nisn', $studentItem);
            $this->assertArrayNotHasKey('gender', $studentItem);
        }
    }
}
