<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name')->default('FatzDev');
            $table->string('professional_title');
            $table->string('hero_headline');
            $table->text('hero_subheadline')->nullable();
            $table->text('bio_summary_1')->nullable();
            $table->text('bio_summary_2')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('location')->default('Remote · Worldwide');
            $table->string('email')->default('hello@fatzdev.com');
            $table->string('phone')->default('+1 (907) 555-0101');
            $table->string('response_time')->default('Within 24 hours');
            $table->boolean('is_available_for_hire')->default(true);
            $table->string('availability_badge_text')->default('Available for freelance work');
            $table->string('years_experience')->default('5+');
            $table->string('projects_delivered')->default('47+');
            $table->string('client_satisfaction_rate')->default('100%');
            $table->decimal('rating_score', 2, 1)->default(5.0);
            $table->string('rating_platform')->default('Upwork');
            $table->json('primary_stack')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
