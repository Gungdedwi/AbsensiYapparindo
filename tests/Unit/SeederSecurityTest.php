<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SeederSecurityTest extends TestCase
{
    /**
     * Memastikan tidak ada password hardcoded dalam SyntheticDataSeeder.php.
     */
    public function test_no_hardcoded_passwords_in_seeder(): void
    {
        $seederPath = dirname(__DIR__, 2) . '/database/seeders/SyntheticDataSeeder.php';
        $this->assertFileExists($seederPath);

        $content = file_get_contents($seederPath);

        // Memastikan password lama yang di-hardcode tidak ada lagi
        $this->assertStringNotContainsString('Admin_Yapparindo_2026!', $content);
        $this->assertStringNotContainsString('Guru_Sintetis_2026!', $content);

        // Memastikan tidak ada domain riil sekolah di seeder
        $this->assertStringNotContainsString('@yapparindo.sch.id', $content);
        $this->assertStringContainsString('@example.test', $content);
    }

    /**
     * Memastikan SyntheticDataSeeder memverifikasi ADMIN_INITIAL_PASSWORD dan menghentikan eksekusi jika kosong.
     */
    public function test_seeder_throws_when_admin_password_env_is_empty(): void
    {
        $seederPath = dirname(__DIR__, 2) . '/database/seeders/SyntheticDataSeeder.php';
        $content = file_get_contents($seederPath);

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*empty\(\s*\$adminPassword\s*\)\s*\)\s*\{\s*throw\s+new\s+\\\\RuntimeException/s',
            $content,
            'Seeder wajib throw RuntimeException jika ADMIN_INITIAL_PASSWORD kosong.'
        );
    }
}

