<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSignatory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSignatoryController extends Controller
{
    public function index(): View
    {
        return view('admin.school-signatories.index', [
            'schoolSignatories' => SchoolSignatory::query()
                ->orderBy('position')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'position' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        SchoolSignatory::create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('status', 'Penandatangan berhasil ditambahkan.');
    }

    public function toggleActive(SchoolSignatory $schoolSignatory): RedirectResponse
    {
        $schoolSignatory->update(['is_active' => ! $schoolSignatory->is_active]);

        return back()->with('status', 'Status penandatangan diperbarui.');
    }
}
