<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('admin@example.test|127.0.0.1');
        RateLimiter::clear('budi.santoso|127.0.0.1');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SMK Yapparindo');
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertSee('Email atau Nama Pengguna');
    }

    public function test_authenticated_admin_is_redirected_to_admin_dashboard_when_visiting_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_authenticated_teacher_is_redirected_to_teacher_dashboard_when_visiting_login(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->get('/login');

        $response->assertRedirect('/guru/dashboard');
    }

    public function test_user_can_login_using_valid_email(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'username' => 'admin_test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'admin@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_using_valid_username(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@example.test',
            'username' => 'budi.santoso',
            'password' => Hash::make('rahasia_guru_123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'budi.santoso',
            'password' => 'rahasia_guru_123',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'guru@example.test',
            'password' => Hash::make('correct_password'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'guru@example.test',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'nonaktif@example.test',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'nonaktif@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'throttle@example.test',
            'password' => Hash::make('valid_password'),
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'throttle@example.test',
                'password' => 'wrong_attempt',
            ]);
        }

        // Percobaan ke-6 harus diblokir oleh rate limiter
        $response = $this->post('/login', [
            'login' => 'throttle@example.test',
            'password' => 'valid_password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

