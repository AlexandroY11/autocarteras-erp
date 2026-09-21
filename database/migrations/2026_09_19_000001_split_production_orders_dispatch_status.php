<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE production_orders DROP CONSTRAINT IF EXISTS production_orders_status_check");
        DB::statement("ALTER TABLE production_orders ADD CONSTRAINT production_orders_status_check
            CHECK (status IN ('pending','in_progress','done','cancelled'))");

        Schema::table('production_orders', function (Blueprint $table) {
            $table->string('dispatch_status')->nullable()->after('status');
        });

        DB::statement("ALTER TABLE production_orders ADD CONSTRAINT production_orders_dispatch_status_check
            CHECK (dispatch_status IS NULL OR dispatch_status IN
            ('pending_dispatch','dispatched','sent','delivered','returned'))");

        // Salvaguarda: si alguna orden ya estaba en 'done', queda lista para despacho.
        DB::table('production_orders')->where('status', 'done')->update(['dispatch_status' => 'pending_dispatch']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE production_orders DROP CONSTRAINT IF EXISTS production_orders_dispatch_status_check");

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn('dispatch_status');
        });

        DB::statement("ALTER TABLE production_orders DROP CONSTRAINT IF EXISTS production_orders_status_check");
        DB::statement("ALTER TABLE production_orders ADD CONSTRAINT production_orders_status_check
            CHECK (status IN ('pending','in_progress','done','pending_dispatch','dispatched','sent','delivered','returned','cancelled'))");
    }
};
