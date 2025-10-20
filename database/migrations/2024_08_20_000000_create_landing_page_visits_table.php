<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('page');
            $table->date('date');
            $table->unsignedBigInteger('total_visits')->default(0);
            $table->unsignedBigInteger('unique_visits')->default(0);
            $table->unsignedBigInteger('primary_visits')->default(0);
            $table->unsignedBigInteger('secondary_visits')->default(0);
            $table->timestamps();

            $table->unique(['page', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_visits');
    }
};
