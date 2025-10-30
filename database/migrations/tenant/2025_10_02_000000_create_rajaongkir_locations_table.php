<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raja_ongkir_locations', function (Blueprint $table) {
            $table->id();
            $table->string('level', 32);
            $table->string('remote_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('province')->nullable();
            $table->string('province_code')->nullable();
            $table->string('city')->nullable();
            $table->string('city_code')->nullable();
            $table->string('nusa_regency_code')->nullable();
            $table->string('nusa_district_code')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['level', 'remote_id']);
            $table->index('nusa_regency_code');
            $table->index('nusa_district_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raja_ongkir_locations');
    }
};
