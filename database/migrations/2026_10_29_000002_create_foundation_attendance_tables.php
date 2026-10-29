<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sesi Pembelajaran Aktual
        Schema::create('lesson_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->date('date')->index(); // Tanggal sesi di WITA (Asia/Makassar)
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->unsignedTinyInteger('period_start')->nullable(); // Contoh: Jam ke-1
            $table->unsignedTinyInteger('period_end')->nullable();   // Contoh: Jam ke-3
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->timestamps();
        });

        // 2. Jurnal Mengajar dan Materi Per Sesi
        Schema::create('lesson_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_session_id')->unique()->constrained('lesson_sessions')->cascadeOnDelete();
            $table->unsignedInteger('meeting_number'); // Nomor Pertemuan ke-N
            $table->text('topic'); // Materi yang dibahas
            $table->text('notes')->nullable(); // Catatan tambahan / kendala
            $table->timestamp('submitted_at')->nullable(); // Waktu Final Submitted
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Catatan Kehadiran Siswa Per Sesi
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_session_id')->constrained('lesson_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // Status: Hadir (H), Sakit (S), Izin (I), Alpa (A), Training (T), Daily Worker (DW)
            $table->enum('status', ['H', 'S', 'I', 'A', 'T', 'DW'])->index();
            $table->string('notes')->nullable();
            $table->timestamps();

            // Constraint unik: Satu siswa hanya memiliki satu status per sesi
            $table->unique(['lesson_session_id', 'student_id'], 'unique_student_session_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
        Schema::dropIfExists('lesson_journals');
        Schema::dropIfExists('lesson_sessions');
    }
};

