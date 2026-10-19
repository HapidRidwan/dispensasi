<?php

namespace App\Policies;

use App\Models\Dispensation;
use App\Models\StudentEnrollment;
use App\Models\User;

class DispensationPolicy
{
    public function view(User $user, Dispensation $dispensation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($dispensation->applicant_user_id === $user->id) {
            return true;
        }

        if ($user->isSiswa() && $user->student) {
            return $dispensation->recipients()->where('student_id', $user->student->id)->exists();
        }

        if ($user->isGuru() && $user->teacher) {
            $teacherId = $user->teacher->id;

            if ($dispensation->responsibles()->where('teacher_id', $teacherId)->exists()) {
                return true;
            }

            $classIds = $user->teacher->assignments()
                ->where('assignment_type', 'HOMEROOM')
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                ->pluck('class_id');

            $studentIds = StudentEnrollment::whereIn('class_id', $classIds)->where('is_active', true)->pluck('student_id');

            return $dispensation->recipients()->whereIn('student_id', $studentIds)->exists();
        }

        return false;
    }

    public function update(User $user, Dispensation $dispensation): bool
    {
        return $user->isAdmin() || $dispensation->applicant_user_id === $user->id;
    }

    public function verify(User $user, Dispensation $dispensation): bool
    {
        return $user->isGuru();
    }

    public function review(User $user, Dispensation $dispensation): bool
    {
        return $user->isAdmin();
    }
}
