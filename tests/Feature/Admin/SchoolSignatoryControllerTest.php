<?php

namespace Tests\Feature\Admin;

use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\SchoolSignatory;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSignatoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_signatories_and_they_appear_in_activity_forms(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $this->actingAs($admin)
            ->post('/admin/school-signatories', [
                'position' => 'Wakil Kepala Sekolah',
                'name' => 'Dewi Lestari',
            ])
            ->assertRedirect();

        $signatory = SchoolSignatory::where('name', 'Dewi Lestari')->firstOrFail();
        $this->assertTrue($signatory->is_active);

        $this->actingAs($admin)
            ->get('/admin/school-signatories')
            ->assertOk()
            ->assertSee('Dewi Lestari');

        $this->actingAs($admin)
            ->get('/admin/activities/create')
            ->assertOk()
            ->assertSee('Wakil Kepala Sekolah')
            ->assertSee('Dewi Lestari')
            ->assertSee('Pembimbing (opsional)')
            ->assertSee('Pendamping (opsional)');

        $this->actingAs($admin)
            ->get('/admin/dispensations/create')
            ->assertOk()
            ->assertSee('Dewi Lestari');

        $teacher = User::where('email', 'guru@sekolah.test')->firstOrFail();
        $this->actingAs($teacher)
            ->get('/guru/activities/create')
            ->assertOk()
            ->assertSee('Dewi Lestari');

        $this->actingAs($teacher)
            ->get('/guru/dispensations/create')
            ->assertOk()
            ->assertSee('Dewi Lestari');
    }

    public function test_admin_can_deactivate_a_signatory(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $signatory = SchoolSignatory::create([
            'position' => 'Ketua Organisasi',
            'name' => 'Ayu Putri',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post("/admin/school-signatories/{$signatory->id}/toggle-active")
            ->assertRedirect();

        $this->assertDatabaseHas('school_signatories', [
            'id' => $signatory->id,
            'is_active' => false,
        ]);
    }

    public function test_activity_signatories_are_saved_and_shown_on_printable_document(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $signatory = SchoolSignatory::create([
            'position' => 'Kepala Sekolah',
            'name' => 'Dewi Lestari',
            'is_active' => true,
        ]);
        $type = DispensationType::where('category', DispensationType::CATEGORY_ACTIVITY)->firstOrFail();
        $student = Student::where('nis', '12001')->firstOrFail();
        $teacher = Teacher::where('user_id', User::where('email', 'guru@sekolah.test')->value('id'))->firstOrFail();
        $mentor = Teacher::where('user_id', User::where('email', 'guru2@sekolah.test')->value('id'))->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/activities', [
                'type_id' => $type->id,
                'title' => 'Kegiatan dengan Tanda Tangan',
                'reason' => 'Kegiatan resmi sekolah.',
                'date_start' => now()->toDateString(),
                'student_ids' => [$student->id],
                'signatory_ids' => [$signatory->id],
                'responsibles' => [
                    ['teacher_id' => $teacher->id, 'responsibility_type' => 'PENANGGUNG_JAWAB'],
                    ['teacher_id' => $mentor->id, 'responsibility_type' => 'PEMBIMBING'],
                ],
            ])
            ->assertRedirect();

        $dispensation = Dispensation::where('title', 'Kegiatan dengan Tanda Tangan')->firstOrFail();
        $this->assertSame([
            ['position' => 'Kepala Sekolah', 'name' => 'Dewi Lestari'],
        ], $dispensation->signature_details);

        $this->actingAs($admin)
            ->post("/admin/dispensations/{$dispensation->id}/generate-document")
            ->assertRedirect();

        $document = $dispensation->document()->firstOrFail();
        $this->actingAs($admin)
            ->get("/admin/documents/{$document->id}/print")
            ->assertOk()
            ->assertSee('Kepala Sekolah')
            ->assertSee('Dewi Lestari')
            ->assertSee('PEMBIMBING')
            ->assertSee('PENANGGUNG JAWAB')
            ->assertSee($teacher->user->name)
            ->assertSee($mentor->user->name);
    }

    public function test_activity_requires_at_least_one_teacher_responsible(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $type = DispensationType::where('category', DispensationType::CATEGORY_ACTIVITY)->firstOrFail();
        $student = Student::where('nis', '12001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/activities', [
                'type_id' => $type->id,
                'title' => 'Kegiatan Tanpa Penanggung Jawab',
                'reason' => 'Kegiatan resmi sekolah.',
                'date_start' => now()->toDateString(),
                'student_ids' => [$student->id],
                'responsibles' => [
                    ['teacher_id' => null, 'responsibility_type' => 'PENANGGUNG_JAWAB'],
                    ['teacher_id' => null, 'responsibility_type' => 'PEMBIMBING'],
                ],
            ])
            ->assertSessionHasErrors('responsibles');

        $this->assertDatabaseMissing('dispensations', ['title' => 'Kegiatan Tanpa Penanggung Jawab']);
    }

    public function test_student_can_choose_an_active_signatory_for_a_dispensation(): void
    {
        $this->seed();
        $studentUser = User::where('email', 'siswa@sekolah.test')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)->firstOrFail();
        $signatory = SchoolSignatory::create([
            'position' => 'Sekretaris Organisasi',
            'name' => 'Ayu Putri',
            'is_active' => true,
        ]);

        $this->actingAs($studentUser)
            ->get('/siswa/dispensations/create')
            ->assertOk()
            ->assertSee('Sekretaris Organisasi')
            ->assertSee('Ayu Putri');

        $this->actingAs($studentUser)
            ->post('/siswa/dispensations', [
                'type_id' => $type->id,
                'title' => 'Kegiatan Organisasi',
                'reason' => 'Mengikuti kegiatan organisasi.',
                'date_start' => now()->toDateString(),
                'signatory_ids' => [$signatory->id],
            ])
            ->assertRedirect();

        $dispensation = Dispensation::where('title', 'Kegiatan Organisasi')->firstOrFail();
        $this->assertSame([
            ['position' => 'Sekretaris Organisasi', 'name' => 'Ayu Putri'],
        ], $dispensation->signature_details);
    }

    public function test_inactive_signatories_cannot_be_selected(): void
    {
        $this->seed();
        $studentUser = User::where('email', 'siswa@sekolah.test')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)->firstOrFail();
        $signatory = SchoolSignatory::create([
            'position' => 'Wakil Kepala Sekolah',
            'name' => 'Nonaktif',
            'is_active' => false,
        ]);

        $this->actingAs($studentUser)
            ->post('/siswa/dispensations', [
                'type_id' => $type->id,
                'title' => 'Dispensasi Tidak Valid',
                'reason' => 'Validasi daftar penandatangan.',
                'date_start' => now()->toDateString(),
                'signatory_ids' => [$signatory->id],
            ])
            ->assertSessionHasErrors('signatory_ids.0');

        $this->assertDatabaseMissing('dispensations', ['title' => 'Dispensasi Tidak Valid']);
    }

    public function test_non_admin_cannot_manage_school_signatories(): void
    {
        $this->seed();
        $studentUser = User::where('email', 'siswa@sekolah.test')->firstOrFail();

        $this->actingAs($studentUser)
            ->get('/admin/school-signatories')
            ->assertForbidden();
    }
}
