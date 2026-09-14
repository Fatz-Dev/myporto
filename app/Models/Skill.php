<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
{
    protected $fillable = [
        'category_id',
        'icon_class',
        'subtitle',
        'name',
        'level',
        'core_tools',
        'discipline_protocol',
        'description',
        'order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'core_tools' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }
}
