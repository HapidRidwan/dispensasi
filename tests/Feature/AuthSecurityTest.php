<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_passkeys_routes_are_not_available(): void
    {
        $this->get('/passkeys/login/options')->assertNotFound();
        $this->post('/passkeys/login', [])->assertNotFound();
        $this->get('/passkeys/confirm/options')->assertNotFound();
        $this->post('/passkeys/confirm', [])->assertNotFound();
        $this->get('/user/passkeys/options')->assertNotFound();
        $this->post('/user/passkeys', [])->assertNotFound();
    }

    public function test_login_with_valid_password_works(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'guru']);

        $this->post('/login', ['username' => $user->username, 'password' => 'salah-password']);

        $this->assertGuest();
    }

    public function test_admin_without_2fa_is_redirected_from_admin_area(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->first();

        $this->actingAs($admin)->get('/admin/reports')
            ->assertRedirect(route('admin.profile'));
    }

    public function test_admin_with_2fa_can_access_admin_area(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => now()]);

        $this->actingAs($admin)->get('/admin/reports')->assertOk();
    }

    public function test_guru_and_siswa_are_not_forced_to_have_2fa(): void
    {
        $this->seed();

        $guru = User::where('email', 'guru@sekolah.test')->first();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($guru)->get('/guru/dispensations')->assertOk();
        $this->actingAs($siswa)->get('/siswa/dashboard')->assertOk();
    }

    public function test_non_admin_cannot_create_accounts(): void
    {
        $this->seed();

        $guru = User::where('email', 'guru@sekolah.test')->first();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($guru)->post('/admin/users', ['name' => 'X', 'email' => 'x@x.test', 'role' => 'siswa', 'password' => 'password123'])->assertForbidden();
        $this->actingAs($siswa)->post('/admin/users', ['name' => 'X', 'email' => 'x@x.test', 'role' => 'siswa', 'password' => 'password123'])->assertForbidden();
        $this->actingAs($guru)->post('/admin/students', [])->assertForbidden();
        $this->actingAs($siswa)->post('/admin/teachers', [])->assertForbidden();
    }
}
