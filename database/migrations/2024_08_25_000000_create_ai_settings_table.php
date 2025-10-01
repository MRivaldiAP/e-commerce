<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('section')->unique();
            $table->string('provider')->default('openai');
            $table->string('model')->nullable();
            $table->decimal('temperature', 4, 2)->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->decimal('top_p', 4, 2)->nullable();
            $table->decimal('frequency_penalty', 4, 2)->nullable();
            $table->decimal('presence_penalty', 4, 2)->nullable();
            $table->json('extra_settings')->nullable();
            $table->timestamps();
        });

        DB::table('ai_settings')->insert([
            [
                'section' => 'article_generation',
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.70,
                'max_tokens' => 2048,
                'top_p' => 1.00,
                'frequency_penalty' => 0.00,
                'presence_penalty' => 0.00,
                'extra_settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'report_reader',
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.30,
                'max_tokens' => 1500,
                'top_p' => 1.00,
                'frequency_penalty' => 0.00,
                'presence_penalty' => 0.00,
                'extra_settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
