<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    public const STATUS_GENERATED = 'GENERATED';

    public const STATUS_WAITING_SIGNATURE = 'WAITING_SIGNATURE';

    public const STATUS_SIGNED = 'SIGNED';

    public const STATUS_STAMPED = 'STAMPED';

    public const STATUS_FINAL = 'FINAL';

    protected $fillable = [
        'dispensation_id',
        'document_number',
        'document_type',
        'file_path',
        'document_status',
        'generated_at',
        'signed_at',
        'stamped_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'signed_at' => 'datetime',
            'stamped_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function dispensation(): BelongsTo
    {
        return $this->belongsTo(Dispensation::class);
    }

    public function endorsements(): HasMany
    {
        return $this->hasMany(DocumentEndorsement::class);
    }
}
