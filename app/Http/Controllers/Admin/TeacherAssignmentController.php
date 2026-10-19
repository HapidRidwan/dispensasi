<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        return view('admin.teacher-assignments.index', compact('assignments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required_if:assignment_type,HOMEROOM', 'nullable', 'exists:classes,id'],
            'assignment_type' => ['required', 'in:HOMEROOM,OTHER'],
        ]);

        $assignmentData = $request->only(['teacher_id', 'class_id', 'assignment_type']);

        if ($request->assignment_type === 'HOMEROOM') {
            $schoolClass = SchoolClass::findOrFail($request->integer('class_id'));
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
