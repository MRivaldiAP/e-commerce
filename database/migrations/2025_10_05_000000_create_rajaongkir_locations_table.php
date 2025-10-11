<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rajaongkir_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_type', 20);
            $table->string('external_id', 50);
            $table->string('name');
            $table->string('slug')->index();
            $table->string('full_slug')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('province')->nullable();
            $table->string('province_slug')->nullable()->index();
            $table->string('province_id', 50)->nullable();
            $table->string('city_name')->nullable();
            $table->string('city_slug')->nullable()->index();
            $table->string('city_external_id', 50)->nullable()->index();
            $table->string('postal_code', 20)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['location_type', 'external_id']);
            $table->index(['location_type', 'slug']);
            $table->index(['location_type', 'province_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rajaongkir_locations');
    }
};
