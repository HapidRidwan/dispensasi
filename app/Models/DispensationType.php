<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispensationType extends Model
{
    public const CATEGORY_STUDENT = 'STUDENT';

    public const CATEGORY_ACTIVITY = 'ACTIVITY';

    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function dispensations(): HasMany
    {
        return $this->hasMany(Dispensation::class, 'type_id');
    }
}
