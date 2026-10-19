<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::query()
            ->with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('nip', 'like', '%'.$search.'%')
                    ->orWhere('position', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('username', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'username' => Str::lower($data['username']),
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'role' => 'guru',
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'is_active' => true,
            ]);
        });

        return back()->with('status', 'Akun guru dibuat.');
    }

    public function toggleActive(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['is_active' => ! $teacher->is_active]);
        $teacher->user->update(['is_active' => $teacher->is_active]);

        return back()->with('status', 'Status guru diperbarui.');
    }
}
