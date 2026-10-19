<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityDispensationRequest;
use App\Http\Requests\StoreStudentDispensationRequest;
use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ApprovalService;
use App\Services\DispensationService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispensationController extends Controller
{
    public function index(Request $request)
    {
        $dispensations = Dispensation::query()
            ->with(['applicant', 'type', 'recipients.student.user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhere('document_number', 'like', '%'.$search.'%')
                    ->orWhereHas('applicant', fn ($user) => $user->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('type', fn ($type) => $type->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('recipients.student.user', fn ($user) => $user->where('name', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.dispensations.index', [
            'dispensations' => $dispensations,
        ]);
    }

    public function create(): View
    {
        return view('admin.dispensations.create', [
            'types' => DispensationType::query()
                ->where('category', DispensationType::CATEGORY_STUDENT)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'students' => Student::query()->with('user')->orderBy('nis')->get(),
        ]);
    }

    public function store(StoreStudentDispensationRequest $request, DispensationService $service): RedirectResponse
    {
        $dispensation = $service->createStudentDispensation(
            $request->user(),
            $request->validated(),
            $request->file('file'),
        );

        return redirect()->route('admin.dispensations.show', $dispensation)
            ->with('status', 'Dispensasi siswa dibuat dan disetujui.');
    }

    public function createActivity(): View
    {
        return view('admin.dispensations.create-activity', [
            'types' => DispensationType::query()
                ->where('category', DispensationType::CATEGORY_ACTIVITY)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'students' => Student::query()->with(['user', 'enrollments.class'])->orderBy('nis')->get(),
            'teachers' => Teacher::query()->with('user')->where('is_active', true)->get(),
        ]);
    }

    public function storeActivity(StoreActivityDispensationRequest $request, DispensationService $service): RedirectResponse
    {
        $dispensation = $service->createActivity(
            $request->user(),
            $request->validated(),
            $request->file('file'),
        );

        return redirect()->route('admin.dispensations.show', $dispensation)
            ->with('status', 'Kegiatan formal dibuat dan disetujui.');
    }

    public function show(Dispensation $dispensation)
    {
        return view('admin.dispensations.show', [
            'dispensation' => $dispensation->load(['applicant', 'type', 'recipients.student.user', 'responsibles.teacher.user', 'attachments', 'approvals.reviewer', 'document.endorsements', 'logs.user']),
        ]);
    }

    public function approve(Request $request, Dispensation $dispensation, ApprovalService $approvals): RedirectResponse
    {
        $this->authorize('review', $dispensation);

        $approvals->adminReview($dispensation, $request->user(), true, $request->notes);

        return back()->with('status', 'Dispensasi disetujui.');
    }

    public function reject(Request $request, Dispensation $dispensation, ApprovalService $approvals): RedirectResponse
    {
        $this->authorize('review', $dispensation);

        $request->validate(['notes' => ['required', 'string']]);

        $approvals->adminReview($dispensation, $request->user(), false, $request->notes);

        return back()->with('status', 'Dispensasi ditolak.');
    }

    public function generateDocument(Request $request, Dispensation $dispensation, DocumentService $documents, ApprovalService $approvals): RedirectResponse
    {
        $this->authorize('review', $dispensation);

        $document = $documents->generate($dispensation);
        $approvals->markDocumentGenerated($dispensation, $request->user(), $document->document_number);

        return back()->with('status', 'PDF dibuat: '.$document->document_number);
    }
}
