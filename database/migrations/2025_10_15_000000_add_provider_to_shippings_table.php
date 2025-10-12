<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (! Schema::hasColumn('shippings', 'provider')) {
                $table->string('provider')->default('manual')->after('order_id');
            }
        });

        if (Schema::hasColumn('shippings', 'provider')) {
            DB::table('shippings')
                ->whereNull('provider')
                ->update(['provider' => 'manual']);
        }
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (Schema::hasColumn('shippings', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
