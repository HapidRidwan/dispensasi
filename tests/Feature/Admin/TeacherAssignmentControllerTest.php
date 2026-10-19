<?php

namespace Tests\Feature\Admin;

use RuntimeException;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_assigns_a_homeroom_teacher_for_the_whole_school_year(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $teacher = Teacher::where('user_id', User::where('email', 'guru@sekolah.test')->value('id'))->firstOrFail();
        $schoolClass = SchoolClass::where('name', 'XII RPL 1')->firstOrFail();
        TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $schoolClass->id)
            ->where('assignment_type', 'HOMEROOM')
            ->delete();

        $this->actingAs($admin)->post('/admin/teacher-assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $schoolClass->id,
            'assignment_type' => 'HOMEROOM',
        ])->assertRedirect();

        $assignment = TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $schoolClass->id)
            ->where('assignment_type', 'HOMEROOM')
            ->firstOrFail();

        $this->assertSame('2026-07-01', $assignment->start_date->toDateString());
        $this->assertSame('2027-06-30', $assignment->end_date->toDateString());
        $this->assertTrue($assignment->is_active);
    }

    public function test_admin_cannot_assign_a_homeroom_teacher_to_a_class_without_a_school_year(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $teacher = Teacher::where('user_id', User::where('email', 'guru@sekolah.test')->value('id'))->firstOrFail();
        $schoolClass = SchoolClass::where('name', 'XII RPL 1')->firstOrFail();
        $schoolClass->update(['academic_year' => 'Tahun 2026']);
        $assignmentCount = TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $schoolClass->id)
            ->where('assignment_type', 'HOMEROOM')
            ->count();

        $this->actingAs($admin)->post('/admin/teacher-assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $schoolClass->id,
            'assignment_type' => 'HOMEROOM',
        ])->assertSessionHasErrors('class_id');

        $this->assertSame($assignmentCount, TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $schoolClass->id)
            ->where('assignment_type', 'HOMEROOM')
            ->count());
    }

    public function test_migration_backfills_missing_homeroom_dates_from_the_school_year(): void
    {
        $this->seed();
        $assignment = TeacherAssignment::firstOrFail();
        $assignment->update(['start_date' => null, 'end_date' => null]);
        $migration = require database_path('migrations/2026_10_18_052533_backfill_homeroom_assignment_dates.php');

        $migration->up();

        $assignment->refresh();
        $this->assertSame('2026-07-01', $assignment->start_date->toDateString());
        $this->assertSame('2027-06-30', $assignment->end_date->toDateString());
    }

    public function test_migration_fails_when_a_homeroom_assignment_has_an_invalid_school_year(): void
    {
        $this->seed();
        $assignment = TeacherAssignment::firstOrFail();
        $assignment->update(['start_date' => null, 'end_date' => null]);
        $assignment->class()->update(['academic_year' => 'Tahun 2026']);
        $migration = require database_path('migrations/2026_10_18_052533_backfill_homeroom_assignment_dates.php');

        $this->expectException(RuntimeException::class);

        $migration->up();
    }
}
