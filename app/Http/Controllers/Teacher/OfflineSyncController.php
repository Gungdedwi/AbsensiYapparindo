<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LessonJournal;
use App\Models\LessonSession;
use App\Models\StudentAttendance;
use App\Models\SyncLog;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OfflineSyncController extends Controller
{
    /**
     * Unduh paket data offline minimum (jadwal penugasan dan roster siswa 1 minggu ke depan).
     * Sesuai prinsip data minimization: hanya mengirim id dan name siswa/guru.
     */
    public function bundle(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $user?->teacher;

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
            ])
            ->get();

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
            ],
            'range' => [
                'start' => $startDate,
                'end' => $endDate,
                'timezone' => 'Asia/Makassar',
            ],
            'assignments' => $assignments->map(fn ($assignment) => [
                'id' => $assignment->id,
                'subject' => [
                    'id' => $assignment->subject?->id,
                    'name' => $assignment->subject?->name,
                    'code' => $assignment->subject?->code,
                ],
                'classroom' => [
                    'id' => $assignment->classroom?->id,
                    'name' => $assignment->classroom?->name,
                    'grade_level' => $assignment->classroom?->grade_level,
                    'students' => $assignment->classroom?->students->map(fn ($student) => [
                        'id' => $student->id,
                        'name' => $student->name,
                    ])->values()->all() ?? [],
                ],
            ])->values()->all(),
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Endpoint sinkronisasi batch idempotent dari antrean IndexedDB offline.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        if (! $teacher) {
            return response()->json(['error' => 'Data guru tidak ditemukan.'], 403);
        }

        // Batasi maksimal 50 item per pengiriman
        $request->validate([
            'items' => ['required', 'array', 'max:50'],
        ], [
            'items.required' => 'Daftar item sinkronisasi wajib disertakan.',
            'items.max' => 'Maksimal 50 item per pengiriman sinkronisasi.',
        ]);

        $items = $request->input('items', []);
        $results = [];

        foreach ($items as $item) {
            $idempotencyKey = $item['idempotency_key'] ?? null;

            if (! $idempotencyKey || ! is_string($idempotencyKey)) {
                $results[] = [
                    'idempotency_key' => null,
                    'status' => 'rejected',
                    'message' => 'Idempotency key wajib disertakan.',
                    'errors' => ['idempotency_key' => ['Idempotency key wajib disertakan dan berupa string.']],
                ];
                continue;
            }

            // 1. Cek Idempotensi Per-User (user_id + idempotency_key)
            // Hanya status 'synced' dan 'conflict' yang bersifat final. Status failed/rejected boleh di-retry.
            $existingLog = SyncLog::where('user_id', $user->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingLog && in_array($existingLog->status, ['synced', 'conflict'], true)) {
                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => $existingLog->status,
                    'message' => 'Sudah diproses sebelumnya (idempotent acknowledgement).',
                    'session_id' => $existingLog->lesson_session_id,
                ];
                continue;
            }

            // 2. Validasi Struktur & Input Per Item (menyamakan aturan AttendanceSessionController::store)
            $periodStart = isset($item['period_start']) && is_numeric($item['period_start'])
                ? (int) $item['period_start']
                : 1;

            $validator = Validator::make($item, [
                'teaching_assignment_id' => ['required', 'integer'],
                'date' => ['required', 'date'],
                'time_start' => ['required', 'date_format:H:i'],
                'time_end' => ['required', 'date_format:H:i', 'after:time_start'],
                'period_start' => ['required', 'integer', 'min:1', 'max:16'],
                'period_end' => ['required', 'integer', 'min:'.$periodStart, 'max:16'],
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

            if ($validator->fails()) {
                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'rejected',
                        'message' => 'Validasi input gagal.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'rejected',
                    'message' => 'Validasi data gagal.',
                    'errors' => $validator->errors()->toArray(),
                ];
                continue;
            }

            // 3. Validasi Otorisasi Penugasan Mengajar (Proteksi IDOR Penugasan)
            $assignment = TeachingAssignment::with('classroom')->find($item['teaching_assignment_id']);
            if (! $assignment || $assignment->teacher_id !== $teacher->id) {
                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'rejected',
                        'message' => 'Akses ditolak. Penugasan bukan milik guru yang sedang login.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'rejected',
                    'message' => 'Akses ditolak. Penugasan bukan milik guru yang sedang login.',
                    'errors' => ['teaching_assignment_id' => ['Akses ditolak untuk penugasan mengajar ini.']],
                ];
                continue;
            }

            // 4. Validasi Keanggotaan Siswa Aktif (Proteksi IDOR Siswa & Kelengkapan Absensi)
            $activeStudentIds = $assignment->classroom->students()
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $submittedStudentIds = array_map('intval', array_keys($item['attendance']));
            $invalidStudentIds = array_diff($submittedStudentIds, $activeStudentIds);
            $missingStudentIds = array_diff($activeStudentIds, $submittedStudentIds);

            if (! empty($invalidStudentIds)) {
                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'rejected',
                        'message' => 'Terdapat siswa yang bukan anggota aktif di kelas penugasan ini.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'rejected',
                    'message' => 'Terdapat siswa yang bukan anggota aktif di kelas penugasan ini.',
                    'errors' => ['attendance' => ['Terdapat siswa yang bukan anggota aktif di kelas penugasan ini.']],
                ];
                continue;
            }

            if (! empty($missingStudentIds)) {
                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'rejected',
                        'message' => 'Seluruh siswa aktif di kelas ini wajib memiliki status kehadiran.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'rejected',
                    'message' => 'Seluruh siswa aktif di kelas ini wajib memiliki status kehadiran.',
                    'errors' => ['attendance' => ['Seluruh siswa aktif di kelas ini wajib memiliki status kehadiran.']],
                ];
                continue;
            }

            // 5. Deteksi Konflik (Server-Wins, D-025)
            $existingServerSession = LessonSession::with('journal.submitter')
                ->where('teaching_assignment_id', $assignment->id)
                ->whereDate('date', $item['date'])
                ->where('period_start', (int) $item['period_start'])
                ->first();

            if ($existingServerSession) {
                $submitterName = $existingServerSession->journal?->submitter?->name;
                $byInfo = $submitterName ? " oleh {$submitterName}" : '';
                $conflictMessage = "Data presensi kelas {$assignment->classroom->name} pada tanggal {$item['date']} jam ke-{$item['period_start']} sudah ada di server{$byInfo}.";

                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'lesson_session_id' => $existingServerSession->id,
                        'status' => 'conflict',
                        'message' => $conflictMessage,
                        'payload' => $item,
                    ]
                );

                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\Notifications\SyncConflictNotification',
                    'data' => json_encode([
                        'title' => 'Konflik Sinkronisasi Absensi',
                        'message' => $conflictMessage,
                        'assignment_id' => $assignment->id,
                        'date' => $item['date'],
                        'conflicted_at' => now()->toIso8601String(),
                    ]),
                    'read_at' => null,
                ]);

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'conflict',
                    'message' => "Data server dipertahankan (server-wins). {$conflictMessage}",
                    'session_id' => $existingServerSession->id,
                ];
                continue;
            }

            // 6. Simpan Data Secara Atomik dengan Penanganan Konkurensi & Constraint
            try {
                $session = DB::transaction(function () use ($item, $assignment, $user, $idempotencyKey) {
                    $session = LessonSession::create([
                        'teaching_assignment_id' => $assignment->id,
                        'date' => $item['date'],
                        'time_start' => $item['time_start'],
                        'time_end' => $item['time_end'],
                        'period_start' => $item['period_start'],
                        'period_end' => $item['period_end'],
                        'status' => 'completed',
                    ]);

                    LessonJournal::create([
                        'lesson_session_id' => $session->id,
                        'meeting_number' => $item['meeting_number'],
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

                    SyncLog::updateOrCreate(
                        ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                        [
                            'lesson_session_id' => $session->id,
                            'status' => 'synced',
                            'message' => 'Berhasil disinkronkan ke server.',
                            'payload' => $item,
                        ]
                    );

                    return $session;
                });

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'synced',
                    'message' => 'Sesi pembelajaran dan presensi berhasil disinkronkan.',
                    'session_id' => $session->id,
                ];
            } catch (UniqueConstraintViolationException|QueryException $e) {
                // Tangani konkurensi request ganda bersamaan atau tabrakan sesi
                $duplicateLog = SyncLog::where('user_id', $user->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($duplicateLog && in_array($duplicateLog->status, ['synced', 'conflict'], true)) {
                    $results[] = [
                        'idempotency_key' => $idempotencyKey,
                        'status' => $duplicateLog->status,
                        'message' => 'Sudah diproses sebelumnya (idempotent acknowledgement).',
                        'session_id' => $duplicateLog->lesson_session_id,
                    ];
                    continue;
                }

                // Cek apakah terjadi tabrakan unik pada lesson_sessions
                $concurrentSession = LessonSession::with('journal.submitter')
                    ->where('teaching_assignment_id', $assignment->id)
                    ->whereDate('date', $item['date'])
                    ->where('period_start', (int) $item['period_start'])
                    ->first();

                if ($concurrentSession) {
                    $submitterName = $concurrentSession->journal?->submitter?->name;
                    $byInfo = $submitterName ? " oleh {$submitterName}" : '';
                    $conflictMessage = "Data presensi kelas {$assignment->classroom->name} pada tanggal {$item['date']} jam ke-{$item['period_start']} sudah ada di server{$byInfo}.";

                    SyncLog::updateOrCreate(
                        ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                        [
                            'lesson_session_id' => $concurrentSession->id,
                            'status' => 'conflict',
                            'message' => $conflictMessage,
                            'payload' => $item,
                        ]
                    );

                    $results[] = [
                        'idempotency_key' => $idempotencyKey,
                        'status' => 'conflict',
                        'message' => "Data server dipertahankan (server-wins). {$conflictMessage}",
                        'session_id' => $concurrentSession->id,
                    ];
                    continue;
                }

                // Jika kegagalan database lain
                Log::error('Offline sync database constraint/query error', [
                    'user_id' => $user->id,
                    'idempotency_key' => $idempotencyKey,
                    'error_code' => $e->getCode(),
                ]);

                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'failed',
                        'message' => 'Terjadi kendala konsistensi data di server.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'failed',
                    'message' => 'Terjadi kendala konsistensi data di server.',
                ];
            } catch (\Throwable $e) {
                // Catat detail ke log aplikasi secara aman (tanpa data siswa)
                Log::error('Offline sync unexpected transaction error', [
                    'user_id' => $user->id,
                    'idempotency_key' => $idempotencyKey,
                    'exception' => get_class($e),
                ]);

                SyncLog::updateOrCreate(
                    ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
                    [
                        'status' => 'failed',
                        'message' => 'Terjadi kesalahan sistem internal di server.',
                        'payload' => $item,
                    ]
                );

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'failed',
                    'message' => 'Terjadi kesalahan sistem internal di server.',
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
