<?php

namespace App\Http\Controllers;

use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, PushNotificationService $push): RedirectResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'p256dh' => ['required', 'string'],
            'auth' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'browser' => ['nullable', 'string', 'max:255'],
        ]);

        $push->register($request->user(), $request->only(['endpoint', 'p256dh', 'auth', 'device_name', 'browser']));

        return back()->with('status', 'Push subscription tersimpan.');
    }

    public function destroy(Request $request, PushNotificationService $push): RedirectResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        $push->remove($request->user(), $request->endpoint);

        return back()->with('status', 'Push subscription dihapus.');
    }
}
