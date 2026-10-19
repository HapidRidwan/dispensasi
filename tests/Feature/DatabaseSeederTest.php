<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_renames_the_legacy_piket_account_without_creating_a_duplicate_teacher(): void
    {
        $this->seed();
        $teacherUser = User::where('username', 'guru2')->firstOrFail();
        $teacherId = Teacher::where('user_id', $teacherUser->id)->value('id');
        $teacherUser->update(['username' => 'piket', 'email' => 'piket@sekolah.test']);

        $this->seed();

        $this->assertDatabaseMissing('users', ['username' => 'piket']);
        $this->assertDatabaseHas('users', [
            'id' => $teacherUser->id,
            'username' => 'guru2',
            'email' => 'guru2@sekolah.test',
            'role' => 'guru',
        ]);
        $this->assertSame(1, Teacher::where('user_id', $teacherUser->id)->count());
        $this->assertSame($teacherId, Teacher::where('user_id', $teacherUser->id)->value('id'));
    }
}
