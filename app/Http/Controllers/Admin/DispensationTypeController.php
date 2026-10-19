<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DispensationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DispensationTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = DispensationType::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.dispensation-types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:STUDENT,ACTIVITY'],
            'description' => ['nullable', 'string'],
        ]);

        DispensationType::create($request->only(['name', 'category', 'description']) + ['is_active' => true]);

        return back()->with('status', 'Jenis dispensasi dibuat.');
    }

    public function update(Request $request, DispensationType $dispensationType): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:STUDENT,ACTIVITY'],
            'description' => ['nullable', 'string'],
        ]);

        $dispensationType->update($request->only(['name', 'category', 'description']));

        return back()->with('status', 'Jenis dispensasi diperbarui.');
    }

    public function toggleActive(DispensationType $dispensationType): RedirectResponse
    {
        $dispensationType->update(['is_active' => ! $dispensationType->is_active]);

        return back()->with('status', 'Status diperbarui.');
    }
}
