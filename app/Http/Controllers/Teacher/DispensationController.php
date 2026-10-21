<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityDispensationRequest;
use App\Http\Requests\StoreStudentDispensationRequest;
use App\Http\Requests\VerifyDispensationRequest;
use App\Models\Dispensation;
use App\Models\TeacherAssignment;
use App\Services\ApprovalService;
use App\Services\DispensationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DispensationController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        $homeroomClassIds = $teacher?->assignments()
            ->where('assignment_type', TeacherAssignment::TYPE_HOMEROOM)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->select('class_id');

        return view('guru.dispensations.index', [
            'dispensations' => Dispensation::query()
                ->with(['applicant', 'type', 'recipients.student.user'])
                ->where(function ($query) use ($request, $teacher, $homeroomClassIds) {
                    $query->where('applicant_user_id', $request->user()->id);

                    if ($teacher) {
                        $query->orWhereHas('recipients.student.enrollments', fn ($enrollments) => $enrollments
                            ->where('is_active', true)
                            ->whereIn('class_id', $homeroomClassIds))
                            ->orWhereHas('responsibles', fn ($responsibles) => $responsibles
                                ->where('teacher_id', $teacher->id));
                    }
                })
                ->when($request->filled('q'), function ($query) use ($request) {
                    $search = $request->string('q')->trim()->toString();
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhereHas('type', fn ($type) => $type->where('name', 'like', '%'.$search.'%'));
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function indexActivities(Request $request)
    {
        $teacherId = $request->user()->teacher?->id;

        return view('guru.activities.index', [
            'activities' => Dispensation::query()
                ->with(['type', 'recipients'])
                ->withCount('recipients')
                ->whereHas('responsibles', fn ($query) => $query->where('teacher_id', $teacherId ?? 0))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $search = $request->string('q')->trim()->toString();
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhereHas('type', fn ($type) => $type->where('name', 'like', '%'.$search.'%'));
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(Dispensation $dispensation)
    {
        $this->authorize('view', $dispensation);

        return view('guru.dispensations.show', [
            'dispensation' => $dispensation->load(['applicant', 'type', 'recipients.student.user', 'responsibles.teacher.user', 'attachments', 'approvals.reviewer', 'document', 'logs.user']),
        ]);
    }

    public function store(StoreStudentDispensationRequest $request, DispensationService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->isGuru()) {
            $data['needs_guru_verification'] = false;
        }

        $service->createStudentDispensation($request->user(), $data, $request->file('file'));

        return back()->with('status', 'Dispensasi siswa dibuat.');
    }

    public function showActivity(Dispensation $dispensation)
    {
        $this->authorize('view', $dispensation);

        return view('guru.activities.show', [
            'activity' => $dispensation->load(['type', 'recipients.student.user', 'responsibles.teacher.user', 'approvals.reviewer']),
        ]);
    }

    public function storeActivity(StoreActivityDispensationRequest $request, DispensationService $service): RedirectResponse
    {
        $service->createActivity($request->user(), $request->validated(), $request->file('file'));

        return back()->with('status', 'Dispensasi kegiatan dibuat.');
    }

    public function verify(VerifyDispensationRequest $request, Dispensation $dispensation, ApprovalService $approvals): RedirectResponse
    {
        $this->authorize('verify', $dispensation);

        $approvals->teacherVerify($dispensation, $request->user(), (bool) $request->approved, $request->notes);

        return back()->with('status', 'Verifikasi guru tercatat.');
    }
}
