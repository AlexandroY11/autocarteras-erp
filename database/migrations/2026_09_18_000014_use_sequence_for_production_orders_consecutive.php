<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE SEQUENCE IF NOT EXISTS production_orders_consecutive_seq");
        DB::statement("SELECT setval('production_orders_consecutive_seq', COALESCE((SELECT MAX(consecutive) FROM production_orders), 0) + 1, false)");
        DB::statement("ALTER TABLE production_orders ALTER COLUMN consecutive SET DEFAULT nextval('production_orders_consecutive_seq')");
        DB::statement("ALTER SEQUENCE production_orders_consecutive_seq OWNED BY production_orders.consecutive");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE production_orders ALTER COLUMN consecutive DROP DEFAULT");
        DB::statement("DROP SEQUENCE IF EXISTS production_orders_consecutive_seq");
    }
};
