<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (! Schema::hasColumn('shippings', 'provider')) {
                $table->string('provider')->default('manual')->after('order_id');
            }

            if (! Schema::hasColumn('shippings', 'service')) {
                $table->string('service')->nullable()->after('courier');
            }

            if (Schema::hasColumn('shippings', 'status')) {
                $table->string('status', 50)->default('packing')->change();
            }

            if (! Schema::hasColumn('shippings', 'remote_id')) {
                $table->string('remote_id')->nullable()->after('status');
            }

            if (! Schema::hasColumn('shippings', 'meta')) {
                $table->json('meta')->nullable()->after('remote_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (Schema::hasColumn('shippings', 'provider')) {
                $table->dropColumn('provider');
            }

            if (Schema::hasColumn('shippings', 'meta')) {
                $table->dropColumn('meta');
            }

            if (Schema::hasColumn('shippings', 'remote_id')) {
                $table->dropColumn('remote_id');
            }

            if (Schema::hasColumn('shippings', 'service')) {
                $table->dropColumn('service');
            }
        });
    }
};
