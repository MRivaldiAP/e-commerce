<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            if (Schema::hasColumn('page_settings', 'page')) {
                $table->dropUnique('page_settings_page_key_unique');
            }

            $table->string('theme')->nullable()->after('page');
        });

        Schema::table('page_settings', function (Blueprint $table) {
            $table->unique(['theme', 'page', 'key'], 'page_settings_theme_page_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropUnique('page_settings_theme_page_key_unique');
            $table->dropColumn('theme');
        });

        Schema::table('page_settings', function (Blueprint $table) {
            $table->unique(['page', 'key']);
        });
    }
};
