<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = [
        'role_title',
        'company_name',
        'location',
        'start_period',
        'end_period',
        'is_current',
        'description',
        'company_logo_url',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }
}
