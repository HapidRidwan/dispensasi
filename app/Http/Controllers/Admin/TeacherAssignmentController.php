<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = TeacherAssignment::query()
            ->with(['teacher.user', 'class'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('assignment_type', 'like', '%'.$search.'%')
                    ->orWhereHas('teacher.user', fn ($user) => $user->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('class', fn ($class) => $class
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('academic_year', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.teacher-assignments.index', [
            'assignments' => $assignments,
            'teachers' => Teacher::query()->with('user')->where('is_active', true)->orderBy('id')->get(),
            'classes' => SchoolClass::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', Rule::exists('teachers', 'id')->where('is_active', true)],
            'class_id' => ['required_if:assignment_type,HOMEROOM', 'nullable', 'exists:classes,id'],
            'assignment_type' => ['required', Rule::in([TeacherAssignment::TYPE_HOMEROOM, TeacherAssignment::TYPE_DUTY, TeacherAssignment::TYPE_OTHER])],
            'day_of_week' => ['required_if:assignment_type,DUTY', 'nullable', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'start_time' => ['required_if:assignment_type,DUTY', 'nullable', 'date_format:H:i'],
            'end_time' => ['required_if:assignment_type,DUTY', 'nullable', 'date_format:H:i', 'after:start_time'],
        ]);

        $assignmentData = [
            'teacher_id' => $data['teacher_id'],
            'assignment_type' => $data['assignment_type'],
            'class_id' => $data['assignment_type'] === TeacherAssignment::TYPE_HOMEROOM ? $data['class_id'] : null,
            'day_of_week' => $data['assignment_type'] === TeacherAssignment::TYPE_DUTY ? $data['day_of_week'] : null,
            'start_time' => $data['assignment_type'] === TeacherAssignment::TYPE_DUTY ? $data['start_time'] : null,
            'end_time' => $data['assignment_type'] === TeacherAssignment::TYPE_DUTY ? $data['end_time'] : null,
        ];

        if ($data['assignment_type'] === TeacherAssignment::TYPE_HOMEROOM) {
            $schoolClass = SchoolClass::findOrFail((int) $data['class_id']);
            preg_match('/^(\d{4})\s*[\/-]\s*(\d{4})$/', $schoolClass->academic_year, $years);

            if (count($years) !== 3 || (int) $years[2] !== (int) $years[1] + 1) {
                return back()->withErrors(['class_id' => 'Tahun ajaran kelas harus berformat YYYY/YYYY, misalnya 2026/2027.'])->withInput();
            }

            $assignmentData['start_date'] = $years[1].'-07-01';
            $assignmentData['end_date'] = $years[2].'-06-30';
        }

        TeacherAssignment::create($assignmentData + ['is_active' => true]);

        return back()->with('status', 'Penugasan guru dibuat.');
    }

    public function destroy(TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $teacherAssignment->update(['is_active' => false]);

        return back()->with('status', 'Penugasan dinonaktifkan.');
    }
}
