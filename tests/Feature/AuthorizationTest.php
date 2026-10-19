<?php

namespace Tests\Feature;

use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_cannot_access_admin(): void
    {
        $this->seed();
        $guru = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($guru)->get('/admin/users')->assertForbidden();
        $this->actingAs($guru)->get('/admin/activities/create')->assertForbidden();
    }

    public function test_siswa_cannot_access_guru_endpoints(): void
    {
        $this->seed();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($siswa)->get('/guru/dispensations')->assertForbidden();
        $this->actingAs($siswa)->get('/guru/picket')->assertNotFound();
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_siswa_cannot_view_other_student_dispensation_via_policy(): void
    {
        $this->seed();

        $siswaUser = User::where('email', 'siswa@sekolah.test')->first();
        $otherStudent = Student::where('user_id', '!=', $siswaUser->id)->first();
        $type = DispensationType::first();

        $d = Dispensation::create([
            'applicant_user_id' => $otherStudent->user_id,
            'type_id' => $type->id,
            'title' => 'Rahasia',
            'reason' => 'X',
            'date_start' => now()->toDateString(),
            'status' => 'SUBMITTED',
        ]);
        $d->recipients()->create(['student_id' => $otherStudent->id]);

        $this->assertFalse($siswaUser->can('view', $d));
    }

    public function test_guru_dispensation_for_self_not_allowed(): void
    {
        $this->seed();
        $guru = User::where('email', 'guru@sekolah.test')->first();

        // Endpoint siswa/dispensations dikhususkan role siswa
        $this->actingAs($guru)->post('/siswa/dispensations', [])->assertForbidden();
    }
}
