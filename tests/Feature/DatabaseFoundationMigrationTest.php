<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\SyntheticDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan seluruh tabel fondasi berhasil dibuat di database in-memory (SQLite)
     * dan tabel daily_agendas atau kehadiran pegawai tidak ada.
     */
    public function test_database_migrations_create_all_foundation_tables_and_exclude_daily_agendas(): void
    {
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
            $this->assertTrue(
                Schema::hasTable($table),
                "Tabel fondasi '{$table}' harus terbentuk setelah migration."
            );
        }

        // Memastikan tabel yang DILARANG di Fase 2 benar-benar tidak terbentuk
        $this->assertFalse(
            Schema::hasTable('daily_agendas'),
            "Tabel 'daily_agendas' TIDAK BOLEH ada di Fase 2."
        );
        $this->assertFalse(
            Schema::hasTable('staff_attendance'),
            "Tabel 'staff_attendance' TIDAK BOLEH ada di Fase 2."
        );
    }

    /**
     * Memastikan seeder berhasil mengeksekusi data sintetis saat ADMIN_INITIAL_PASSWORD diset.
     */
    public function test_seeder_succeeds_when_admin_initial_password_is_set(): void
    {
        putenv('ADMIN_INITIAL_PASSWORD=TestAdminPassword2026!');
        $_ENV['ADMIN_INITIAL_PASSWORD'] = 'TestAdminPassword2026!';

        $this->seed(SyntheticDataSeeder::class);

        // Verifikasi akun admin
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin, 'Akun admin harus berhasil dibuat oleh seeder.');
        $this->assertSame('admin@example.test', $admin->email);
        $this->assertSame('admin', $admin->username);
        $this->assertTrue(Hash::check('TestAdminPassword2026!', $admin->password));

        // Verifikasi data guru sintetis
        $this->assertSame(3, Teacher::count(), 'Harus ada 3 guru contoh sintetis.');

        // Verifikasi data siswa sintetis
        $this->assertSame(5, Student::count(), 'Harus ada 5 siswa contoh sintetis.');
    }

    /**
     * Memastikan seeder melempar RuntimeException dan menghentikan proses jika ADMIN_INITIAL_PASSWORD kosong.
     */
    public function test_seeder_fails_when_admin_initial_password_is_empty(): void
    {
        putenv('ADMIN_INITIAL_PASSWORD=');
        $_ENV['ADMIN_INITIAL_PASSWORD'] = '';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_INITIAL_PASSWORD belum diset di environment. Seeder dihentikan demi keamanan.');

        $this->seed(SyntheticDataSeeder::class);
    }
}

