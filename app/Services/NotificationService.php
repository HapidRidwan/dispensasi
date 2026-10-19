<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Dispensation;
use App\Models\Notification;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyUsers(iterable $userIds, string $title, string $message, string $type = 'INFO', ?int $dispensationId = null): void
    {
        foreach (collect($userIds)->unique() as $userId) {
            $user = User::find($userId);

            if (! $user || ! $user->is_active) {
                continue;
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'dispensation_id' => $dispensationId,
                'title' => $title,
                'message' => $message,
                'notification_type' => $type,
            ]);

            event(new NotificationCreated($notification));

            app(PushNotificationService::class)->sendToUser($user, $title, $message);
        }
    }

    public function homeroomTeacherUserIds(Student $student): Collection
    {
        $classIds = $student->enrollments()->where('is_active', true)->pluck('class_id');

        return TeacherAssignment::where('assignment_type', TeacherAssignment::TYPE_HOMEROOM)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->whereIn('class_id', $classIds)
            ->with('teacher.user')
            ->get()
            ->pluck('teacher.user_id')
            ->filter()
            ->values();
    }

    public function responsibleUserIds(Dispensation $dispensation): Collection
    {
        return $dispensation->responsibles()->with('teacher.user')->get()
            ->pluck('teacher.user_id')->filter()->values();
    }

    public function adminUserIds(): Collection
    {
        return User::where('role', User::ROLE_ADMIN)->where('is_active', true)->pluck('id');
    }

    public function recipientUserIds(Dispensation $dispensation): Collection
    {
        return $dispensation->recipients()->with('student.user')->get()
            ->pluck('student.user_id')->filter()->values();
    }

    /**
     * Notifikasi setelah siswa mengajukan dispensasi pribadi.
     */
    public function notifyStudentSubmitted(Dispensation $dispensation): void
    {
        $recipient = $dispensation->recipients()->with('student')->first();

        if ($recipient) {
            $this->notifyUsers($this->homeroomTeacherUserIds($recipient->student), 'Pengajuan Dispensasi Baru', 'Siswa mengajukan dispensasi: '.$dispensation->title, 'DISPENSATION', $dispensation->id);
        }
    }

    /**
     * Notifikasi setelah guru memverifikasi dispensasi siswa.
     */
    public function notifyTeacherVerified(Dispensation $dispensation): void
    {
        $this->notifyUsers($this->adminUserIds(), 'Dispensasi Siap Diverifikasi', 'Dispensasi "'.$dispensation->title.'" telah diverifikasi guru dan menunggu Admin/TU.', 'DISPENSATION', $dispensation->id);
    }

    /**
     * Notifikasi setelah admin menyetujui/menolak.
     */
    public function notifyAdminDecision(Dispensation $dispensation, bool $approved): void
    {
        $title = $approved ? 'Dispensasi Disetujui' : 'Dispensasi Ditolak';

        $targets = collect([$dispensation->applicant_user_id])
            ->merge($this->recipientUserIds($dispensation))
            ->merge($this->responsibleUserIds($dispensation));

        foreach ($dispensation->recipients()->with('student')->get() as $recipient) {
            $targets = $targets->merge($this->homeroomTeacherUserIds($recipient->student));
        }

        $this->notifyUsers($targets->filter(), $title, 'Dispensasi "'.$dispensation->title.'" '.($approved ? 'disetujui oleh Admin/TU.' : 'ditolak oleh Admin/TU.'), 'DISPENSATION', $dispensation->id);
    }

    /**
     * Notifikasi untuk kegiatan formal ke semua pihak terkait.
     */
    public function notifyActivityApproved(Dispensation $dispensation): void
    {
        $targets = collect()
            ->merge($this->responsibleUserIds($dispensation))
            ->merge($this->recipientUserIds($dispensation));

        foreach ($dispensation->recipients()->with('student')->get() as $recipient) {
            $targets = $targets->merge($this->homeroomTeacherUserIds($recipient->student));
        }

        $this->notifyUsers($targets->filter(), 'Kegiatan Disetujui', 'Kegiatan "'.$dispensation->title.'" telah disetujui.', 'ACTIVITY', $dispensation->id);
    }
}
