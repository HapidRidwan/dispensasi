<?php

namespace Tests\Feature\Student;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_search_only_shows_matching_own_notifications(): void
    {
        $this->seed();
        $student = User::where('email', 'siswa@sekolah.test')->firstOrFail();
        $teacher = User::where('email', 'guru@sekolah.test')->firstOrFail();
        Notification::create([
            'user_id' => $student->id,
            'title' => 'Izin Kelas Disetujui',
            'message' => 'Pengajuan izin kelas Anda telah disetujui.',
            'notification_type' => 'DISPENSATION',
        ]);
        Notification::create([
            'user_id' => $student->id,
            'title' => 'Jadwal Ujian',
            'message' => 'Periksa jadwal ujian terbaru.',
            'notification_type' => 'INFO',
        ]);
        Notification::create([
            'user_id' => $teacher->id,
            'title' => 'Izin Guru',
            'message' => 'Pengajuan izin guru.',
            'notification_type' => 'DISPENSATION',
        ]);

        $response = $this->actingAs($student)->get('/siswa/notifications?q=izin');

        $response->assertSee('Izin Kelas Disetujui');
        $response->assertDontSee('Izin Guru');
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->count() === 1
            && $notifications->first()->title === 'Izin Kelas Disetujui');
    }
}
