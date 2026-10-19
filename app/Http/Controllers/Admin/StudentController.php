<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query()
            ->with(['user', 'enrollments.class'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('nis', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('username', 'like', '%'.$search.'%'))
                        ->orWhereHas('enrollments.class', fn ($class) => $class->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'username' => Str::lower($data['username']),
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'role' => 'siswa',
                'is_active' => true,
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);

            if (! empty($data['class_id'])) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $data['class_id'],
                    'academic_year' => $data['academic_year'] ?? date('Y').'/'.(date('Y') + 1),
                    'start_date' => now()->toDateString(),
                    'is_active' => true,
                ]);
            }
        });

        return back()->with('status', 'Akun siswa dibuat.');
    }

    public function toggleActive(Student $student): RedirectResponse
    {
        $student->update(['is_active' => ! $student->is_active]);
        $student->user->update(['is_active' => $student->is_active]);

        return back()->with('status', 'Status siswa diperbarui.');
    }
}
