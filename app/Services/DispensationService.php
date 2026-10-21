<?php

namespace App\Services;

use App\Models\Dispensation;
use App\Models\DispensationAttachment;
use App\Models\SchoolSignatory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DispensationService
{
    public function __construct(
        protected ApprovalService $approvals,
        protected NotificationService $notifications,
    ) {}

    public function createStudentDispensation(User $applicant, array $data, ?UploadedFile $file = null): Dispensation
    {
        return DB::transaction(function () use ($applicant, $data, $file) {
            $dispensation = Dispensation::create([
                'applicant_user_id' => $applicant->id,
                'type_id' => $data['type_id'],
                'picket_assignment_id' => $data['picket_assignment_id'] ?? null,
                'permit_direction' => $data['permit_direction'] ?? 'KELUAR',
                'other_type' => $data['other_type'] ?? null,
                'title' => $data['title'],
                'reason' => $data['reason'],
                'date_start' => $data['date_start'],
                'date_end' => $data['date_end'] ?? null,
                'time_start' => $data['time_start'] ?? null,
                'time_end' => $data['time_end'] ?? null,
                'location' => $data['location'] ?? null,
                'destination' => $data['destination'] ?? null,
                'status' => Dispensation::STATUS_SUBMITTED,
                'signature_details' => $this->signatureDetails($data['signatory_ids'] ?? []),
            ]);

            $studentIds = $data['student_ids'] ?? [];

            if (empty($studentIds) && $applicant->isSiswa() && $applicant->student) {
                $studentIds = [$applicant->student->id];
            }

            foreach ($studentIds as $studentId) {
                $dispensation->recipients()->create(['student_id' => $studentId, 'status' => 'PENDING']);
            }

            if ($file) {
                $this->storeAttachment($dispensation, $applicant, $file);
            }

            $this->approvals->log($dispensation, $applicant, 'CREATED', null, $dispensation->status, 'Pengajuan dibuat.');

            $needGuruStage = $applicant->isSiswa() && ($data['needs_guru_verification'] ?? true);

            if ($needGuruStage) {
                $dispensation->approvals()->create([
                    'reviewer_id' => $applicant->id,
                    'stage' => 'GURU',
                    'status' => 'PENDING',
                ]);

                $this->notifications->notifyStudentSubmitted($dispensation);
            } elseif ($applicant->isAdmin()) {
                $this->approvals->adminReview($dispensation, $applicant, true, 'Dibuat dan disetujui Admin/TU.');
            } else {
                $dispensation->update(['status' => Dispensation::STATUS_ADMIN_REVIEW]);
                $this->approvals->log($dispensation, $applicant, 'SUBMITTED_TO_ADMIN', Dispensation::STATUS_SUBMITTED, $dispensation->status, 'Dibuat guru, langsung ke Admin/TU.');
                $this->notifications->notifyUsers($this->notifications->adminUserIds(), 'Pengajuan Baru', 'Pengajuan "'.$dispensation->title.'" menunggu verifikasi Admin/TU.', 'DISPENSATION', $dispensation->id);
            }

            return $dispensation->fresh();
        });
    }

    public function createActivity(User $applicant, array $data, ?UploadedFile $file = null): Dispensation
    {
        return DB::transaction(function () use ($applicant, $data, $file) {
            $dispensation = Dispensation::create([
                'applicant_user_id' => $applicant->id,
                'type_id' => $data['type_id'],
                'other_type' => $data['other_type'] ?? null,
                'title' => $data['title'],
                'reason' => $data['reason'],
                'date_start' => $data['date_start'],
                'date_end' => $data['date_end'] ?? null,
                'time_start' => $data['time_start'] ?? null,
                'time_end' => $data['time_end'] ?? null,
                'location' => $data['location'] ?? null,
                'destination' => $data['destination'] ?? null,
                'status' => Dispensation::STATUS_ADMIN_REVIEW,
                'signature_details' => $this->signatureDetails($data['signatory_ids'] ?? []),
            ]);

            foreach ($data['student_ids'] as $studentId) {
                $dispensation->recipients()->create(['student_id' => $studentId, 'status' => 'PENDING']);
            }

            foreach ($data['responsibles'] ?? [] as $resp) {
                if (empty($resp['teacher_id'])) {
                    continue;
                }

                $dispensation->responsibles()->create([
                    'teacher_id' => $resp['teacher_id'],
                    'responsibility_type' => $resp['responsibility_type'],
                    'notes' => $resp['notes'] ?? null,
                ]);
            }

            if ($file) {
                $this->storeAttachment($dispensation, $applicant, $file);
            }

            $this->approvals->log($dispensation, $applicant, 'CREATED', null, $dispensation->status, 'Dispensasi kegiatan dibuat.');

            if ($applicant->isAdmin()) {
                $this->approvals->adminReview($dispensation, $applicant, true, 'Kegiatan dibuat dan disetujui Admin/TU.');
            } else {
                $this->notifications->notifyUsers($this->notifications->adminUserIds(), 'Kegiatan Baru', 'Kegiatan "'.$dispensation->title.'" menunggu verifikasi Admin/TU.', 'ACTIVITY', $dispensation->id);
            }

            return $dispensation->fresh();
        });
    }

    public function storeAttachment(Dispensation $dispensation, User $uploader, UploadedFile $file): DispensationAttachment
    {
        $path = $file->store('dispensations/'.$dispensation->id, 'public');

        return $dispensation->attachments()->create([
            'uploaded_by' => $uploader->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType() ?? $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * @param  array<int, int|string>  $signatoryIds
     * @return array<int, array{position: string, name: string}>
     */
    private function signatureDetails(array $signatoryIds): array
    {
        if ($signatoryIds === []) {
            return [];
        }

        $signatories = SchoolSignatory::query()
            ->whereIn('id', $signatoryIds)
            ->get()
            ->keyBy('id');

        return collect($signatoryIds)
            ->map(fn (int|string $id): ?array => $signatories->has($id)
                ? [
                    'position' => $signatories->get($id)->position,
                    'name' => $signatories->get($id)->name,
                ]
                : null)
            ->filter()
            ->values()
            ->all();
    }
}
