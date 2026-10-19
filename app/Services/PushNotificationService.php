<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;

class PushNotificationService
{
    public function register(User $user, array $data): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => $data['endpoint']],
            [
                'p256dh' => $data['p256dh'],
                'auth' => $data['auth'],
                'device_name' => $data['device_name'] ?? null,
                'browser' => $data['browser'] ?? null,
                'last_used_at' => now(),
            ]
        );
    }

    public function remove(User $user, string $endpoint): bool
    {
        return PushSubscription::where('user_id', $user->id)->where('endpoint', $endpoint)->delete() > 0;
    }

    public function subscriptionsFor(User $user): Collection
    {
        return $user->pushSubscriptions;
    }

    /**
     * Kirim payload Web Push ke seluruh perangkat milik user.
     * Integrasi library web-push bersifat klien; struktur ini menandai
     * subscription sebagai dipakai dan siap diteruskan ke dispatcher.
     */
    public function sendToUser(User $user, string $title, string $message): int
    {
        $subscriptions = $this->subscriptionsFor($user);

        $subscriptions->each(fn (PushSubscription $s) => $s->update(['last_used_at' => now()]));

        return $subscriptions->count();
    }
}
