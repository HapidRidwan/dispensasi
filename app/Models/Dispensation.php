<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dispensation extends Model
{
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUS_TEACHER_VERIFIED = 'TEACHER_VERIFIED';

    public const STATUS_ADMIN_REVIEW = 'ADMIN_REVIEW';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_PDF_GENERATED = 'PDF_GENERATED';

    public const STATUS_WAITING_ENDORSEMENT = 'WAITING_ENDORSEMENT';

    public const STATUS_FINALIZED = 'FINALIZED';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'applicant_user_id',
        'type_id',
        'other_type',
        'title',
        'reason',
        'date_start',
        'date_end',
        'time_start',
        'time_end',
        'location',
        'destination',
        'status',
        'rejection_reason',
        'document_number',
    ];

    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DispensationType::class, 'type_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(DispensationRecipient::class);
    }

    public function responsibles(): HasMany
    {
        return $this->hasMany(DispensationResponsible::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DispensationAttachment::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DispensationApproval::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DispensationLog::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(DispensationAttendance::class);
    }
}
