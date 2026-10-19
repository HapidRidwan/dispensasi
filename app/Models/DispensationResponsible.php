<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensationResponsible extends Model
{
    public const TYPE_PENANGGUNG_JAWAB = 'PENANGGUNG_JAWAB';

    public const TYPE_PEMBIMBING = 'PEMBIMBING';

    public const TYPE_PENDAMPING = 'PENDAMPING';

    protected $fillable = [
        'dispensation_id',
        'teacher_id',
        'responsibility_type',
        'notes',
    ];

    public function dispensation(): BelongsTo
    {
        return $this->belongsTo(Dispensation::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
