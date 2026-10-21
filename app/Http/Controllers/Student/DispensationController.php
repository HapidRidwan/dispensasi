<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentDispensationRequest;
use App\Models\Dispensation;
use App\Services\DispensationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispensationController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->user()->student?->id;
        $search = $request->string('q')->trim()->toString();

        return view('siswa.dispensations.index', [
            'dispensations' => Dispensation::query()
                ->where(function ($query) use ($request, $studentId) {
                    $query->where('applicant_user_id', $request->user()->id)
                        ->orWhereHas('recipients', fn ($recipients) => $recipients->where('student_id', $studentId));
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhereHas('type', fn ($type) => $type->where('name', 'like', '%'.$search.'%'));
                    });
                })
                ->with(['type', 'recipients.student.user', 'document'])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(Dispensation $dispensation)
    {
        $this->authorize('view', $dispensation);

        return view('siswa.dispensations.show', [
            'dispensation' => $dispensation->load(['type', 'picketAssignment.teacher.user', 'recipients.student.user', 'recipients.student.enrollments.class', 'attachments', 'approvals.reviewer', 'document', 'logs.user']),
        ]);
    }

    public function print(Dispensation $dispensation): View
    {
        $this->authorize('view', $dispensation);
        abort_unless(in_array($dispensation->status, [
            Dispensation::STATUS_APPROVED,
            Dispensation::STATUS_PDF_GENERATED,
            Dispensation::STATUS_WAITING_ENDORSEMENT,
            Dispensation::STATUS_FINALIZED,
            Dispensation::STATUS_COMPLETED,
        ], true), 404);

        return view('siswa.dispensations.print', [
            'dispensation' => $dispensation->load([
                'type',
                'picketAssignment.teacher.user',
                'recipients.student.user',
                'recipients.student.enrollments.class',
            ]),
        ]);
    }

    public function store(StoreStudentDispensationRequest $request, DispensationService $service): RedirectResponse
    {
        $data = $request->validated();
        $data['student_ids'] = [$request->user()->student?->id];

        if (empty($data['student_ids'][0])) {
            return back()->withErrors(['student' => 'Profil siswa tidak ditemukan.']);
        }

        $service->createStudentDispensation($request->user(), $data, $request->file('file'));

        return back()->with('status', 'Pengajuan terkirim.');
    }
}
