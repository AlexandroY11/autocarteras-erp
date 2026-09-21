<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->timestamp('created_notified_at')->nullable()->after('created_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('notes');
        });

        Schema::table('order_dispatches', function (Blueprint $table) {
            $table->timestamp('shipped_notified_at')->nullable()->after('dispatched_by');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn('created_notified_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });

        Schema::table('order_dispatches', function (Blueprint $table) {
            $table->dropColumn('shipped_notified_at');
        });
    }
};
