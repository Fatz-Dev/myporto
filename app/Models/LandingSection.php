<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSection extends Model
{
    protected $fillable = [
        'section_key',
        'name',
        'nav_label',
        'is_visible',
        'show_in_navbar',
        'order',
        'custom_title',
        'custom_subtitle',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'show_in_navbar' => 'boolean',
            'order' => 'integer',
            'metadata' => 'array',
        ];
    }
}
