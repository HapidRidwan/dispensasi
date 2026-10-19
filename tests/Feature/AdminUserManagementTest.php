<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_guru_account(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Guru Baru',
            'username' => 'guru.baru',
            'email' => 'guru.baru@sekolah.test',
            'role' => 'guru',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'guru.baru', 'email' => 'guru.baru@sekolah.test', 'role' => 'guru']);
    }

    public function test_admin_can_create_siswa_account(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $class = SchoolClass::where('name', 'XII RPL 1')->firstOrFail();

        $this->actingAs($admin)->post('/admin/students', [
            'name' => 'Siswa Baru',
            'username' => 'siswa.baru',
            'email' => 'siswa.baru@sekolah.test',
            'password' => 'password123',
            'nis' => '99999',
            'class_id' => $class->id,
            'academic_year' => '2026/2027',
        ]);

        $this->assertDatabaseHas('users', ['username' => 'siswa.baru', 'email' => 'siswa.baru@sekolah.test', 'role' => 'siswa']);
        $this->assertDatabaseHas('students', ['nis' => '99999']);
        $student = Student::where('nis', '99999')->firstOrFail();
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_teacher_account_and_profile(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $response = $this->actingAs($admin)->post('/admin/teachers', [
            'name' => 'Guru Baru',
            'username' => 'guru.baru',
            'email' => 'guru.baru@sekolah.test',
            'password' => 'password123',
            'nip' => '1980003',
            'position' => 'Wali Kelas',
        ]);

        $response->assertRedirect();
        $teacherUser = User::where('username', 'guru.baru')->firstOrFail();
        $this->assertDatabaseHas('users', ['username' => 'guru.baru', 'role' => 'guru']);
        $this->assertDatabaseHas('teachers', [
            'user_id' => $teacherUser->id,
            'nip' => '1980003',
            'position' => 'Wali Kelas',
        ]);
    }

    public function test_admin_can_create_class(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $response = $this->actingAs($admin)->post('/admin/classes', [
            'name' => 'X RPL 2',
            'grade' => 'X',
            'major' => 'RPL',
            'academic_year' => '2026/2027',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'name' => 'X RPL 2',
            'grade' => 'X',
            'major' => 'RPL',
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_open_student_teacher_and_class_management_pages(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $this->actingAs($admin)->get('/admin/students')
            ->assertSee('Tambah Siswa')
            ->assertSee('XII RPL 1');

        $this->actingAs($admin)->get('/admin/teachers')
            ->assertSee('Tambah Guru');

        $this->actingAs($admin)->get('/admin/classes')
            ->assertSee('Tambah Kelas');
    }

    public function test_account_without_recovery_email_can_log_in_with_username_and_password(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Guru Tanpa Email',
            'username' => 'guru.username',
            'role' => 'guru',
            'password' => 'password123',
        ])->assertRedirect();

        $account = User::where('username', 'guru.username')->firstOrFail();
        $this->assertNull($account->email);

        $this->post('/logout');
        $this->post('/login', ['username' => 'guru.username', 'password' => 'password123'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($account);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $this->seed();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($siswa)->post('/admin/users', [
            'name' => 'X', 'email' => 'x@x.test', 'role' => 'guru', 'password' => 'password123',
        ])->assertForbidden();
    }

    public function test_siswa_cannot_access_admin_endpoints(): void
    {
        $this->seed();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($siswa)->get('/admin/users')->assertForbidden();
        $this->actingAs($siswa)->get('/admin/reports')->assertForbidden();
    }

    public function test_admin_can_reset_password(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $target = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($admin)->post("/admin/users/{$target->id}/reset-password", ['password' => 'newpassword1'])
            ->assertRedirect();

        $this->assertTrue(Hash::check('newpassword1', $target->fresh()->password));
    }

    public function test_admin_can_filter_accounts_with_search(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/users?q=budi');

        $response->assertSee('Pak Budi');
        $response->assertDontSee('Andi');
    }
}
