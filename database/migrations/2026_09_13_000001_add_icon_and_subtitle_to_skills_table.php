<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            if (!Schema::hasColumn('skills', 'icon_class')) {
                $table->string('icon_class')->nullable()->after('name');
            }
            if (!Schema::hasColumn('skills', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('icon_class');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn(['icon_class', 'subtitle']);
        });
    }
};
