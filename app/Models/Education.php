<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'degree_title',
        'institution_name',
        'location',
        'start_year',
        'end_year',
        'grade',
        'description',
        'skills_acquired',
        'icon_class',
        'credential_id',
        'credential_url',
        'image_url',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'skills_acquired' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }
}
