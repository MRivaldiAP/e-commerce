<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme');
            $table->string('page');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['theme', 'page', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
