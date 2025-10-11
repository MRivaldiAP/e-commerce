<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rajaongkir_subdistricts', function (Blueprint $table) {
            $table->id();
            $table->string('subdistrict_id')->unique();
            $table->string('city_id')->index();
            $table->string('city');
            $table->string('province_id');
            $table->string('province');
            $table->string('type');
            $table->string('subdistrict_name');
            $table->string('nusa_district_code')->nullable()->index();
            $table->string('normalized_slug')->index();
            $table->string('province_slug')->index();
            $table->string('city_slug')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rajaongkir_subdistricts');
    }
};
