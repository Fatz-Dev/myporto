<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'professional_title',
        'hero_headline',
        'hero_subheadline',
        'bio_summary_1',
        'bio_summary_2',
        'avatar_url',
        'cv_url',
        'location',
        'email',
        'phone',
        'response_time',
        'is_available_for_hire',
        'availability_badge_text',
        'years_experience',
        'projects_delivered',
        'client_satisfaction_rate',
        'rating_score',
        'rating_platform',
        'primary_stack',
    ];

    protected function casts(): array
    {
        return [
            'is_available_for_hire' => 'boolean',
            'rating_score' => 'float',
            'primary_stack' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('order');
    }
}
