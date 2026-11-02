<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LessonJournal;
use App\Models\LessonSession;
use App\Models\StudentAttendance;
use App\Models\SyncLog;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfflineSyncController extends Controller
{
    /**
     * Unduh paket data offline (jadwal penugasan dan roster siswa 1 minggu ke depan).
     */
    public function bundle(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            return response()->json(['error' => 'Data guru tidak ditemukan.'], 404);
        }

        $startDate = Carbon::now('Asia/Makassar')->toDateString();
        $endDate = Carbon::now('Asia/Makassar')->addDays(7)->toDateString();

        $assignments = $teacher->teachingAssignments()
            ->where('is_active', true)
            ->with([
                'classroom.students' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
                'subject',
                'academicYear',
            ])
            ->get();

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
            ],
            'range' => [
                'start' => $startDate,
                'end' => $endDate,
                'timezone' => 'Asia/Makassar',
            ],
            'assignments' => $assignments,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Endpoint sinkronisasi batch idempotent dari antrean IndexedDB offline.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            return response()->json(['error' => 'Data guru tidak ditemukan.'], 403);
        }

        $items = $request->input('items', []);
        $results = [];

        foreach ($items as $item) {
            $idempotencyKey = $item['idempotency_key'] ?? null;

            if (! $idempotencyKey) {
                $results[] = [
                    'idempotency_key' => null,
                    'status' => 'failed',
                    'message' => 'Idempotency key wajib disertakan.',
                ];
                continue;
            }

            // 1. Cek Idempotensi: Apakah batch/item ini sudah pernah diproses sebelumnya?
            $existingLog = SyncLog::where('idempotency_key', $idempotencyKey)->first();
            if ($existingLog) {
                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => $existingLog->status,
                    'message' => 'Sudah diproses sebelumnya (idempotent acknowledgement).',
                    'session_id' => $existingLog->lesson_session_id,
                ];
                continue;
            }

            // 2. Validasi Otorisasi Penugasan Mengajar (Proteksi IDOR)
            $assignment = TeachingAssignment::with('classroom')->find($item['teaching_assignment_id'] ?? null);
            if (! $assignment || $assignment->teacher_id !== $teacher->id) {
                SyncLog::create([
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $user->id,
                    'status' => 'failed',
                    'message' => 'Akses ditolak. Penugasan bukan milik guru yang sedang login.',
                    'payload' => $item,
                ]);

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'failed',
                    'message' => 'Akses ditolak untuk penugasan ini.',
                ];
                continue;
            }

            // 3. Deteksi Konflik (Server-Wins, D-025)
            // Cek apakah admin atau pengguna lain telah membuat sesi pada kelas, tanggal, dan jam yang sama di server
            $existingServerSession = LessonSession::where('teaching_assignment_id', $assignment->id)
                ->whereDate('date', $item['date'])
                ->where('period_start', $item['period_start'] ?? 1)
                ->where('status', 'completed')
                ->first();

            if ($existingServerSession) {
                // Sesuai D-025: Data server dipertahankan, dan guru diberi notifikasi sistem
                SyncLog::create([
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $user->id,
                    'lesson_session_id' => $existingServerSession->id,
                    'status' => 'conflict',
                    'message' => 'Konflik terdeteksi: Data server dipertahankan (server-wins).',
                    'payload' => $item,
                ]);

                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\Notifications\SyncConflictNotification',
                    'data' => json_encode([
                        'title' => 'Konflik Sinkronisasi Absensi',
                        'message' => "Data presensi kelas {$assignment->classroom->name} pada tanggal {$item['date']} jam ke-{$item['period_start']} telah dibuat/diubah oleh admin di server.",
                        'assignment_id' => $assignment->id,
                        'date' => $item['date'],
                        'conflicted_at' => now()->toIso8601String(),
                    ]),
                    'read_at' => null,
                ]);

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'conflict',
                    'message' => 'Data server dipertahankan (server-wins). Catatan admin telah ada di server.',
                    'session_id' => $existingServerSession->id,
                ];
                continue;
            }

            // 4. Tidak Ada Konflik: Simpan Data Secara Atomik
            try {
                $session = DB::transaction(function () use ($item, $assignment, $user, $idempotencyKey) {
                    $session = LessonSession::create([
                        'teaching_assignment_id' => $assignment->id,
                        'date' => $item['date'],
                        'time_start' => $item['time_start'] ?? '08:00',
                        'time_end' => $item['time_end'] ?? '09:30',
                        'period_start' => $item['period_start'] ?? 1,
                        'period_end' => $item['period_end'] ?? 2,
                        'status' => 'completed',
                    ]);

                    LessonJournal::create([
                        'lesson_session_id' => $session->id,
                        'meeting_number' => $item['meeting_number'] ?? 1,
                        'topic' => $item['topic'],
                        'notes' => $item['notes'] ?? null,
                        'submitted_at' => now(),
                        'submitted_by' => $user->id,
                    ]);

                    $attendanceNotes = $item['attendance_notes'] ?? [];
                    foreach ($item['attendance'] as $studentId => $status) {
                        StudentAttendance::create([
                            'lesson_session_id' => $session->id,
                            'student_id' => (int) $studentId,
                            'status' => $status,
                            'notes' => $attendanceNotes[$studentId] ?? null,
                        ]);
                    }

                    SyncLog::create([
                        'idempotency_key' => $idempotencyKey,
                        'user_id' => $user->id,
                        'lesson_session_id' => $session->id,
                        'status' => 'synced',
                        'message' => 'Berhasil disinkronkan ke server.',
                        'payload' => $item,
                    ]);

                    return $session;
                });

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                    'message' => 'Sesi pembelajaran dan presensi berhasil disinkronkan.',
                    'session_id' => $session->id,
                ];
            } catch (\Throwable $e) {
                SyncLog::create([
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $user->id,
                    'status' => 'failed',
                    'message' => 'Terjadi kesalahan transaksi server: '.$e->getMessage(),
                    'payload' => $item,
                ]);

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'failed',
                    'message' => 'Gagal memproses transaksi di server.',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'processed_count' => count($results),
            'results' => $results,
        ]);
    }
}

