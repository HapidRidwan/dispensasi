<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    public const TYPE_HOMEROOM = 'HOMEROOM';

    public const TYPE_DUTY = 'DUTY';

    public const TYPE_OTHER = 'OTHER';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'assignment_type',
        'day_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function isScheduledOn(CarbonInterface $date): bool
    {
        $days = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return $this->assignment_type === self::TYPE_DUTY
            && $this->is_active
            && $this->day_of_week === $days[$date->dayOfWeekIso]
            && (! $this->start_date || $date->greaterThanOrEqualTo($this->start_date))
            && (! $this->end_date || $date->lessThanOrEqualTo($this->end_date));
    }
}
