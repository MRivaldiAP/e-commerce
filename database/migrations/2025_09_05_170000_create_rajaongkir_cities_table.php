<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rajaongkir_cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_id')->unique();
            $table->string('province_id');
            $table->string('province');
            $table->string('type');
            $table->string('city_name');
            $table->string('postal_code')->nullable();
            $table->string('nusa_regency_code')->nullable()->index();
            $table->string('normalized_slug')->index();
            $table->string('province_slug')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rajaongkir_cities');
    }
};
