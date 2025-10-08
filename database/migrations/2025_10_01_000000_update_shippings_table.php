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
                $table->string('provider')->default('')->after('order_id');
            }

            if (! Schema::hasColumn('shippings', 'service')) {
                $table->string('service')->nullable()->after('courier');
            }

            if (! Schema::hasColumn('shippings', 'external_id')) {
                $table->string('external_id')->nullable()->after('tracking_number');
            }

            if (! Schema::hasColumn('shippings', 'metadata')) {
                $table->json('metadata')->nullable()->after('estimated_delivery');
            }
        });

        Schema::table('shippings', function (Blueprint $table) {
            $table->string('status', 32)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (Schema::hasColumn('shippings', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('shippings', 'external_id')) {
                $table->dropColumn('external_id');
            }

            if (Schema::hasColumn('shippings', 'service')) {
                $table->dropColumn('service');
            }

            if (Schema::hasColumn('shippings', 'provider')) {
                $table->dropColumn('provider');
            }
        });

        Schema::table('shippings', function (Blueprint $table) {
            $table->string('status', 32)->default('packing')->change();
        });
    }
};
