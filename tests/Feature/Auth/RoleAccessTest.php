<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_teacher_dashboard(): void
    {
        $response = $this->get('/guru/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_teacher_can_access_teacher_dashboard(): void
    {
        $teacherUser = User::factory()->create([
            'name' => 'Budi Santoso, S.Kom.',
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacherUser)->get('/guru/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ruang Kerja Guru');
        $response->assertSee('Budi Santoso, S.Kom.');
        $response->assertSee('WITA');
    }

    public function test_teacher_is_forbidden_from_accessing_admin_dashboard(): void
    {
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacherUser)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Admin Utama Sekolah',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Operasional Sekolah');
        $response->assertSee('Admin Utama Sekolah');
        $response->assertSee('WITA');
    }

    public function test_admin_is_forbidden_from_accessing_teacher_dashboard(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminUser)->get('/guru/dashboard');

        $response->assertStatus(403);
    }

    public function test_root_url_redirects_authenticated_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_root_url_redirects_authenticated_teacher_to_teacher_dashboard(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $response = $this->actingAs($teacher)->get('/');

        $response->assertRedirect('/guru/dashboard');
    }
}

