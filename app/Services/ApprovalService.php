<?php

namespace App\Services;

use App\Models\Dispensation;
use App\Models\DispensationLog;
use App\Models\User;

class ApprovalService
{
    public function log(Dispensation $dispensation, User $user, string $action, ?string $oldStatus, ?string $newStatus, ?string $description = null): DispensationLog
    {
        return DispensationLog::create([
            'dispensation_id' => $dispensation->id,
            'user_id' => $user->id,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    public function teacherVerify(Dispensation $dispensation, User $guru, bool $approved, ?string $notes = null): Dispensation
    {
        $old = $dispensation->status;

        $dispensation->approvals()->create([
            'reviewer_id' => $guru->id,
            'stage' => 'GURU',
            'status' => $approved ? 'APPROVED' : 'REJECTED',
            'notes' => $notes,
            'reviewed_at' => now(),
        ]);

        if ($approved) {
            $dispensation->update(['status' => Dispensation::STATUS_TEACHER_VERIFIED]);
            $this->log($dispensation, $guru, 'GURU_VERIFY', $old, $dispensation->status, $notes);
            app(NotificationService::class)->notifyTeacherVerified($dispensation->fresh());
        } else {
            $dispensation->update(['status' => Dispensation::STATUS_REJECTED, 'rejection_reason' => $notes]);
            $this->log($dispensation, $guru, 'GURU_REJECT', $old, $dispensation->status, $notes);
            app(NotificationService::class)->notifyUsers([$dispensation->applicant_user_id], 'Dispensasi Ditolak Guru', 'Dispensasi "'.$dispensation->title.'" ditolak: '.$notes, 'DISPENSATION', $dispensation->id);
        }

        return $dispensation->fresh();
    }

    public function adminReview(Dispensation $dispensation, User $admin, bool $approved, ?string $notes = null): Dispensation
    {
        if (! $approved && empty($notes)) {
            throw new \InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        $old = $dispensation->status;

        $dispensation->approvals()->create([
            'reviewer_id' => $admin->id,
            'stage' => 'ADMIN',
            'status' => $approved ? 'APPROVED' : 'REJECTED',
            'notes' => $notes,
            'reviewed_at' => now(),
        ]);

        if ($approved) {
            $dispensation->update(['status' => Dispensation::STATUS_APPROVED, 'rejection_reason' => null]);
            $this->log($dispensation, $admin, 'ADMIN_APPROVE', $old, $dispensation->status, $notes);
        } else {
            $dispensation->update(['status' => Dispensation::STATUS_REJECTED, 'rejection_reason' => $notes]);
            $this->log($dispensation, $admin, 'ADMIN_REJECT', $old, $dispensation->status, $notes);
        }

        $freshDispensation = $dispensation->fresh(['type']);

        if ($approved && $freshDispensation->type->category === \App\Models\DispensationType::CATEGORY_ACTIVITY) {
            app(NotificationService::class)->notifyActivityApproved($freshDispensation);
        } else {
            app(NotificationService::class)->notifyAdminDecision($freshDispensation, $approved);
        }

        return $freshDispensation;
    }

    public function markDocumentGenerated(Dispensation $dispensation, User $admin, string $documentNumber): Dispensation
    {
        $old = $dispensation->status;
        $dispensation->update(['status' => Dispensation::STATUS_PDF_GENERATED, 'document_number' => $documentNumber]);
        $this->log($dispensation, $admin, 'PDF_GENERATED', $old, $dispensation->status, 'Nomor dokumen: '.$documentNumber);

        return $dispensation->fresh();
    }
}
