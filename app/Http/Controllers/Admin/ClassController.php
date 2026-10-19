<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('grade', 'like', '%'.$search.'%')
                    ->orWhere('major', 'like', '%'.$search.'%')
                    ->orWhere('academic_year', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.classes.index', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:10'],
            'major' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        SchoolClass::create($request->only(['name', 'grade', 'major', 'academic_year']) + ['is_active' => true]);

        return back()->with('status', 'Kelas dibuat.');
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:10'],
            'major' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $class->update($request->only(['name', 'grade', 'major', 'academic_year']));

        return back()->with('status', 'Kelas diperbarui.');
    }

    public function toggleActive(SchoolClass $class): RedirectResponse
    {
        $class->update(['is_active' => ! $class->is_active]);

        return back()->with('status', 'Status kelas diperbarui.');
    }
}
