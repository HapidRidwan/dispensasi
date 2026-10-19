<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_push_subscription(): void
    {
        $this->seed();
        $user = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($user)->post('/push-subscriptions', [
            'endpoint' => 'https://push.example.com/abc',
            'p256dh' => 'key1',
            'auth' => 'auth1',
            'device_name' => 'HP',
            'browser' => 'Chrome',
        ])->assertRedirect();

        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $user->id, 'endpoint' => 'https://push.example.com/abc']);
    }

    public function test_user_can_have_multiple_devices(): void
    {
        $this->seed();
        $user = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($user)->post('/push-subscriptions', ['endpoint' => 'https://a', 'p256dh' => 'k', 'auth' => 'a']);
        $this->actingAs($user)->post('/push-subscriptions', ['endpoint' => 'https://b', 'p256dh' => 'k', 'auth' => 'a']);

        $this->assertEquals(2, PushSubscription::where('user_id', $user->id)->count());
    }

    public function test_user_can_unregister_subscription(): void
    {
        $this->seed();
        $user = User::where('email', 'guru@sekolah.test')->first();

        $this->actingAs($user)->post('/push-subscriptions', ['endpoint' => 'https://a', 'p256dh' => 'k', 'auth' => 'a']);
        $this->actingAs($user)->delete('/push-subscriptions', ['endpoint' => 'https://a']);

        $this->assertEquals(0, PushSubscription::where('user_id', $user->id)->count());
    }
}
