<?php

namespace Tests\Feature;

use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispensationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_siswa_can_create_own_dispensation(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id,
            'title' => 'Sakit',
            'reason' => 'Demam',
            'date_start' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('dispensations', ['title' => 'Sakit', 'status' => 'SUBMITTED']);

        // Notifikasi ke guru wali kelas
        $guru = User::where('email', 'guru@sekolah.test')->first();
        $this->assertDatabaseHas('notifications', ['user_id' => $guru->id]);
    }

    public function test_siswa_dispensation_always_uses_own_student(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $other = Student::where('user_id', '!=', $siswa->id)->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id,
            'title' => 'Coba',
            'reason' => 'X',
            'date_start' => now()->toDateString(),
            'student_ids' => [$other->id],
        ]);

        $d = Dispensation::latest()->first();
        $this->assertEquals([$siswa->student->id], $d->recipients()->pluck('student_id')->all());
    }

    public function test_guru_can_verify_student_dispensation_and_admin_approves(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id, 'title' => 'Izin', 'reason' => 'Urusan keluarga', 'date_start' => now()->toDateString(),
        ]);

        $d = Dispensation::latest()->first();
        $guru = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($guru)->post("/guru/dispensations/{$d->id}/verify", ['approved' => true])
            ->assertRedirect();

        $this->assertEquals('TEACHER_VERIFIED', $d->fresh()->status);

        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $this->actingAs($admin)->post("/admin/dispensations/{$d->id}/approve", ['notes' => 'OK'])
            ->assertRedirect();

        $this->assertEquals('APPROVED', $d->fresh()->status);
        $this->assertDatabaseHas('dispensation_logs', ['dispensation_id' => $d->id, 'new_status' => 'APPROVED']);
    }

    public function test_admin_rejection_requires_reason(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id, 'title' => 'Izin', 'reason' => 'X', 'date_start' => now()->toDateString(),
        ]);

        $d = Dispensation::latest()->first();
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);

        $this->actingAs($admin)->post("/admin/dispensations/{$d->id}/reject", [])
            ->assertSessionHasErrors('notes');
    }

    public function test_guru_can_create_dispensation_for_students_directly_to_admin(): void
    {
        $guru = User::where('email', 'guru@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();
        $students = Student::limit(2)->pluck('id');

        $this->actingAs($guru)->post('/guru/dispensations', [
            'type_id' => $type->id, 'title' => 'Izin Rombel', 'reason' => 'Kegiatan', 'date_start' => now()->toDateString(), 'student_ids' => $students->all(),
        ])->assertRedirect();

        $d = Dispensation::latest()->first();
        $this->assertEquals('ADMIN_REVIEW', $d->status);
        $this->assertCount(2, $d->recipients);
    }

    public function test_admin_can_add_a_student_category_and_create_an_approved_dispensation_for_students(): void
    {
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $student = Student::where('nis', '12001')->firstOrFail();
        $studentUser = $student->user;
        $guru = User::where('email', 'guru@sekolah.test')->firstOrFail();

        $this->actingAs($admin)->post('/admin/dispensation-types', [
            'name' => 'Keperluan Khusus',
            'category' => DispensationType::CATEGORY_STUDENT,
            'description' => 'Kategori dispensasi tambahan.',
        ])->assertRedirect();

        $type = DispensationType::where('name', 'Keperluan Khusus')->firstOrFail();
        $this->assertDatabaseHas('dispensation_types', [
            'id' => $type->id,
            'category' => DispensationType::CATEGORY_STUDENT,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/dispensations/create')->assertOk()->assertSee('Keperluan Khusus');
        $response = $this->actingAs($admin)->post('/admin/dispensations', [
            'type_id' => $type->id,
            'title' => 'Dispensasi Khusus',
            'reason' => 'Keperluan sekolah.',
            'date_start' => now()->toDateString(),
            'student_ids' => [$student->id],
        ]);

        $dispensation = Dispensation::where('title', 'Dispensasi Khusus')->firstOrFail();

        $response->assertRedirect(route('admin.dispensations.show', $dispensation));
        $this->assertDatabaseHas('dispensations', [
            'id' => $dispensation->id,
            'applicant_user_id' => $admin->id,
            'type_id' => $type->id,
            'status' => Dispensation::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('dispensation_recipients', [
            'dispensation_id' => $dispensation->id,
            'student_id' => $student->id,
        ]);
        $this->assertDatabaseHas('dispensation_approvals', [
            'dispensation_id' => $dispensation->id,
            'reviewer_id' => $admin->id,
            'stage' => 'ADMIN',
            'status' => 'APPROVED',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $studentUser->id,
            'dispensation_id' => $dispensation->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guru->id,
            'dispensation_id' => $dispensation->id,
        ]);

        $this->actingAs($guru)->get('/guru/dispensations')->assertOk()->assertSee('Dispensasi Khusus');
        $this->actingAs($studentUser)->get('/siswa/dispensations')->assertOk()->assertSee('Dispensasi Khusus');
        $this->actingAs($guru)->get('/guru/dashboard')->assertOk()->assertSee('Dispensasi Khusus');
        $this->actingAs($studentUser)->get('/siswa/dashboard')->assertOk()->assertSee('Dispensasi Khusus');
    }

    public function test_admin_cannot_create_a_student_dispensation_with_an_activity_type(): void
    {
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $activityType = DispensationType::where('category', DispensationType::CATEGORY_ACTIVITY)->firstOrFail();
        $student = Student::firstOrFail();

        $this->actingAs($admin)->post('/admin/dispensations', [
            'type_id' => $activityType->id,
            'title' => 'Jenis Tidak Sesuai',
            'reason' => 'Alasan.',
            'date_start' => now()->toDateString(),
            'student_ids' => [$student->id],
        ])->assertSessionHasErrors('type_id');

        $this->assertDatabaseMissing('dispensations', ['title' => 'Jenis Tidak Sesuai']);
    }

    public function test_admin_can_create_and_approve_a_formal_activity_for_students_and_teacher(): void
    {
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $type = DispensationType::where('category', DispensationType::CATEGORY_ACTIVITY)->firstOrFail();
        $student = Student::where('nis', '12001')->firstOrFail();
        $teacher = Teacher::where('user_id', User::where('email', 'guru@sekolah.test')->value('id'))->firstOrFail();

        $this->actingAs($admin)->get('/admin/activities/create')->assertOk();
        $response = $this->actingAs($admin)->post('/admin/activities', [
            'type_id' => $type->id,
            'title' => 'Acara Formal Admin',
            'reason' => 'Mengikuti kegiatan resmi sekolah.',
            'date_start' => now()->toDateString(),
            'student_ids' => [$student->id],
            'responsibles' => [
                ['teacher_id' => $teacher->id, 'responsibility_type' => 'PENANGGUNG_JAWAB'],
            ],
        ]);
        $dispensation = Dispensation::where('title', 'Acara Formal Admin')->firstOrFail();

        $response->assertRedirect(route('admin.dispensations.show', $dispensation));
        $this->assertDatabaseHas('dispensations', [
            'id' => $dispensation->id,
            'type_id' => $type->id,
            'status' => Dispensation::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('dispensation_approvals', [
            'dispensation_id' => $dispensation->id,
            'reviewer_id' => $admin->id,
            'stage' => 'ADMIN',
            'status' => 'APPROVED',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->user_id,
            'dispensation_id' => $dispensation->id,
            'notification_type' => 'ACTIVITY',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->user_id,
            'dispensation_id' => $dispensation->id,
            'notification_type' => 'ACTIVITY',
        ]);
    }

    public function test_guru_can_create_formal_activity_with_cross_class_students_and_responsible(): void
    {
        $guru = User::where('email', 'guru@sekolah.test')->first();
        $type = DispensationType::where('category', 'ACTIVITY')->first();
        $students = Student::pluck('id')->all();
        $otherTeacherUser = User::where('email', 'guru2@sekolah.test')->firstOrFail();
        $otherTeacher = Teacher::where('user_id', $otherTeacherUser->id)->firstOrFail();

        $this->actingAs($guru)->post('/guru/activities', [
            'type_id' => $type->id,
            'title' => 'Lomba Robotik',
            'reason' => 'Kompetisi',
            'date_start' => now()->toDateString(),
            'student_ids' => $students,
            'responsibles' => [
                ['teacher_id' => $guru->teacher->id, 'responsibility_type' => 'PENANGGUNG_JAWAB'],
                ['teacher_id' => $otherTeacher->id, 'responsibility_type' => 'PEMBIMBING'],
            ],
        ])->assertRedirect();

        $d = Dispensation::latest()->first();
        $this->assertCount(4, $d->recipients);
        $this->assertCount(2, $d->responsibles);
        $this->assertEquals('ADMIN_REVIEW', $d->status);

        // Wali kelas, guru penanggung jawab, dan siswa terkait menerima notifikasi.
        $admin = User::where('email', 'admin@sekolah.test')->first();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $this->actingAs($admin)->post("/admin/dispensations/{$d->id}/approve", []);

        $this->assertDatabaseHas('notifications', ['user_id' => $otherTeacherUser->id, 'dispensation_id' => $d->id]);
        foreach ($students as $sid) {
            $student = Student::find($sid);
            $this->assertDatabaseHas('notifications', ['user_id' => $student->user_id, 'dispensation_id' => $d->id]);
        }
    }

    public function test_homeroom_teacher_found_via_enrollment(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $guru = User::where('email', 'guru@sekolah.test')->first();

        $classId = $siswa->student->enrollments()->where('is_active', true)->first()->class_id;
        $this->assertTrue(
            TeacherAssignment::where('assignment_type', 'HOMEROOM')->where('class_id', $classId)->where('teacher_id', $guru->teacher->id)->exists()
        );
    }

    public function test_notifications_created_for_correct_receivers(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id, 'title' => 'N', 'reason' => 'R', 'date_start' => now()->toDateString(),
        ]);

        $this->assertTrue(Notification::whereNotNull('user_id')->where('notification_type', 'DISPENSATION')->exists());
    }

    public function test_audit_log_created_on_status_changes(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id, 'title' => 'L', 'reason' => 'R', 'date_start' => now()->toDateString(),
        ]);

        $d = Dispensation::latest()->first();
        $this->assertDatabaseHas('dispensation_logs', ['dispensation_id' => $d->id, 'action' => 'CREATED']);
    }

    public function test_siswa_can_submit_a_custom_dispensation_type(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)
            ->whereRaw('LOWER(name) = ?', ['lainnya'])
            ->firstOrFail();

        $response = $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id,
            'other_type' => 'Keperluan mendesak',
            'title' => 'Permohonan khusus',
            'reason' => 'Ada kebutuhan di luar daftar.',
            'date_start' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dispensations', [
            'title' => 'Permohonan khusus',
            'type_id' => $type->id,
            'other_type' => 'Keperluan mendesak',
        ]);
    }

    public function test_siswa_must_name_a_custom_dispensation_type(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.test')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)
            ->whereRaw('LOWER(name) = ?', ['lainnya'])
            ->firstOrFail();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id,
            'title' => 'Permohonan khusus tanpa nama',
            'reason' => 'Alasan.',
            'date_start' => now()->toDateString(),
        ])->assertSessionHasErrors('other_type');

        $this->assertDatabaseMissing('dispensations', ['title' => 'Permohonan khusus tanpa nama']);
    }

    public function test_guru_can_submit_an_activity_with_a_custom_type(): void
    {
        $guru = User::where('email', 'guru@sekolah.test')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_ACTIVITY)
            ->whereRaw('LOWER(name) = ?', ['lainnya'])
            ->firstOrFail();
        $student = Student::firstOrFail();

        $response = $this->actingAs($guru)->post('/guru/activities', [
            'type_id' => $type->id,
            'other_type' => 'Olimpiade sains',
            'title' => 'Olimpiade',
            'reason' => 'Mengikuti perlombaan.',
            'date_start' => now()->toDateString(),
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dispensations', [
            'title' => 'Olimpiade',
            'type_id' => $type->id,
            'other_type' => 'Olimpiade sains',
        ]);
    }
}
