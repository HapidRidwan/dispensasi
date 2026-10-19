<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSignatory extends Model
{
    protected $fillable = [
        'position',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
