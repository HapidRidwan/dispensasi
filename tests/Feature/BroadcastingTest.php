<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\DispensationType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_stored_and_broadcast_event_dispatched(): void
    {
        Event::fake([NotificationCreated::class]);

        $this->seed();
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $type = DispensationType::where('category', 'STUDENT')->first();

        $this->actingAs($siswa)->post('/siswa/dispensations', [
            'type_id' => $type->id,
            'title' => 'Tes',
            'reason' => 'R',
            'date_start' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('notifications', ['user_id' => User::where('email', 'guru@sekolah.test')->first()->id]);
        Event::assertDispatched(NotificationCreated::class);
    }

    public function test_event_uses_private_user_channel(): void
    {
        $this->seed();

        $user = User::where('email', 'guru@sekolah.test')->first();
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'T',
            'message' => 'M',
            'notification_type' => 'INFO',
        ]);

        $event = new NotificationCreated($notification);
        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name);

        $this->assertContains('private-user.'.$user->id, $channels->all());
        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
    }

    public function test_admin_notification_uses_private_admin_channel(): void
    {
        $this->seed();
        config()->set('broadcasting.default', 'reverb');
        app(BroadcastManager::class)->forgetDrivers();
        include base_path('routes/channels.php');

        $admin = User::where('email', 'admin@sekolah.test')->first();
        $notification = Notification::create([
            'user_id' => $admin->id,
            'title' => 'T',
            'message' => 'M',
            'notification_type' => 'INFO',
        ]);

        $event = new NotificationCreated($notification);
        $names = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();

        $this->assertContains('private-admin', $names);
    }

    public function test_user_can_only_subscribe_to_own_channel(): void
    {
        $this->seed();
        config()->set('broadcasting.default', 'reverb');
        app(BroadcastManager::class)->forgetDrivers();
        include base_path('routes/channels.php');

        $a = User::where('email', 'guru@sekolah.test')->first();
        $b = User::where('email', 'siswa@sekolah.test')->first();

        $this->actingAs($a)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-user.'.$a->id,
            'socket_id' => '1234.5678',
        ])->assertOk();

        $this->actingAs($a)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-user.'.$b->id,
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }

    public function test_only_admin_can_subscribe_to_admin_channel(): void
    {
        $this->seed();
        config()->set('broadcasting.default', 'reverb');
        app(BroadcastManager::class)->forgetDrivers();
        include base_path('routes/channels.php');

        $admin = User::where('email', 'admin@sekolah.test')->first();
        $guru = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($admin)->postJson('/broadcasting/auth', ['channel_name' => 'private-admin', 'socket_id' => '1234.5678'])->assertOk();
        $this->actingAs($guru)->postJson('/broadcasting/auth', ['channel_name' => 'private-admin', 'socket_id' => '1234.5678'])->assertForbidden();
    }

    public function test_user_notifications_index_only_returns_own(): void
    {
        $this->seed();

        $a = User::where('email', 'guru@sekolah.test')->first();
        $b = User::where('email', 'siswa@sekolah.test')->first();

        $b = User::where('email', 'siswa@sekolah.test')->first();

        $aNotification = Notification::create(['user_id' => 1, 'title' => 'AdminOnly', 'message' => 'M', 'notification_type' => 'INFO']);
        Notification::create(['user_id' => $b->id, 'title' => 'MilikSiswa', 'message' => 'M', 'notification_type' => 'INFO']);

        $response = $this->actingAs($b)->getJson('/siswa/notifications');

        $this->assertStringContainsString('MilikSiswa', $response->getContent());
        $this->assertStringNotContainsString('AdminOnly', $response->getContent());
    }
}
