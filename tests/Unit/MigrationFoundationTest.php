<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MigrationFoundationTest extends TestCase
{
    /**
     * Memastikan file migration fondasi mendefinisikan seluruh tabel yang disetujui.
     */
    public function test_foundation_tables_are_defined_in_migrations(): void
    {
        $migrationFiles = glob(dirname(__DIR__, 2) . '/database/migrations/*.php');
        $allMigrationContent = '';

        foreach ($migrationFiles as $file) {
            $allMigrationContent .= file_get_contents($file) . "\n";
        }

        $expectedTables = [
            'users',
            'teachers',
            'academic_years',
            'classes',
            'students',
            'subjects',
            'teaching_assignments',
            'lesson_sessions',
            'lesson_journals',
            'student_attendance',
            'notifications',
        ];

        foreach ($expectedTables as $table) {
            $this->assertMatchesRegularExpression(
                "/Schema::create\(\s*['\"]" . preg_quote($table, '/') . "['\"]/",
                $allMigrationContent,
                "Tabel fondasi '{$table}' wajib didefinisikan dalam migration."
            );
        }
    }

    /**
     * Memastikan tabel daily_agendas dan kehadiran pegawai TIDAK didefinisikan di fase ini.
     */
    public function test_daily_agendas_and_staff_attendance_are_not_present(): void
    {
        $migrationFiles = glob(dirname(__DIR__, 2) . '/database/migrations/*.php');
        $allMigrationContent = '';

        foreach ($migrationFiles as $file) {
            $allMigrationContent .= file_get_contents($file) . "\n";
        }

        $forbiddenTables = [
            'daily_agendas',
            'staff_attendance',
        ];

        foreach ($forbiddenTables as $table) {
            $this->assertDoesNotMatchRegularExpression(
                "/Schema::create\(\s*['\"]" . preg_quote($table, '/') . "['\"]/",
                $allMigrationContent,
                "Tabel '{$table}' TIDAK BOLEH dibuat pada Fase 2."
            );
        }
    }
}

