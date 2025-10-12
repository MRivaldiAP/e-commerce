<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rajaongkir_provinces', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rajaongkir_cities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('province_id');
            $table->string('province_name');
            $table->string('type');
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->timestamps();

            $table->index('province_id');
        });

        Schema::create('rajaongkir_subdistricts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('city_id');
            $table->string('city_name');
            $table->string('province_id');
            $table->string('province_name');
            $table->string('type')->nullable();
            $table->string('name');
            $table->timestamps();

            $table->index('city_id');
            $table->index('province_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rajaongkir_subdistricts');
        Schema::dropIfExists('rajaongkir_cities');
        Schema::dropIfExists('rajaongkir_provinces');
    }
};
