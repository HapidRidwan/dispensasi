<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentEndorsement extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'endorsement_type',
        'status',
        'notes',
        'endorsed_at',
    ];

    protected function casts(): array
    {
        return ['endorsed_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
