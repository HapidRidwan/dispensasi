<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensationAttendance extends Model
{
    public const ACTION_EXIT = 'EXIT';

    public const ACTION_RETURN = 'RETURN';

    public const ACTION_ENTRY = 'ENTRY';

    public const ACTION_LEAVE = 'LEAVE';

    protected $fillable = [
        'dispensation_id',
        'student_id',
        'recorded_by',
        'action',
        'recorded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    public function dispensation(): BelongsTo
    {
        return $this->belongsTo(Dispensation::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
