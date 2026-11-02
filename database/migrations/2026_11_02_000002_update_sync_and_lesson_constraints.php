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
        // 1. Constraint unik sesi pembelajaran: 1 penugasan pada tanggal dan jam ke-N yang sama tidak boleh ganda
        Schema::table('lesson_sessions', function (Blueprint $table) {
            $table->unique(['teaching_assignment_id', 'date', 'period_start'], 'unique_session_assignment_date_period');
        });

        // 2. Constraint unik sync_logs diubah menjadi gabungan (user_id, idempotency_key)
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->unique(['user_id', 'idempotency_key'], 'unique_user_idempotency_key');
            $table->string('status', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropUnique('unique_user_idempotency_key');
            $table->unique('idempotency_key');
        });

        Schema::table('lesson_sessions', function (Blueprint $table) {
            $table->dropUnique('unique_session_assignment_date_period');
        });
    }
};

