<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        return view('shared.notifications', [
            'notifications' => Notification::where('user_id', $request->user()->id)
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('message', 'like', '%'.$search.'%')
                        ->orWhere('notification_type', 'like', '%'.$search.'%');
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
