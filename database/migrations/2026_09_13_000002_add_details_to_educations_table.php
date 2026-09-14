<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educations', function (Blueprint $table) {
            if (!Schema::hasColumn('educations', 'grade')) {
                $table->string('grade', 100)->nullable()->after('end_year');
            }
            if (!Schema::hasColumn('educations', 'description')) {
                $table->text('description')->nullable()->after('grade');
            }
            if (!Schema::hasColumn('educations', 'skills_acquired')) {
                $table->text('skills_acquired')->nullable()->after('description');
            }
            if (!Schema::hasColumn('educations', 'icon_class')) {
                $table->string('icon_class', 100)->nullable()->after('skills_acquired');
            }
            if (!Schema::hasColumn('educations', 'credential_url')) {
                $table->string('credential_url', 500)->nullable()->after('credential_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('educations', function (Blueprint $table) {
            $table->dropColumn(['grade', 'description', 'skills_acquired', 'icon_class', 'credential_url']);
        });
    }
};
