<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique()->index();
            $table->string('name');
            $table->string('nav_label')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_in_navbar')->default(true);
            $table->integer('order')->default(0)->index();
            $table->string('custom_title')->nullable();
            $table->string('custom_subtitle')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sections');
    }
};
