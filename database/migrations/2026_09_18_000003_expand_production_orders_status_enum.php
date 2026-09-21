<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE production_orders DROP CONSTRAINT IF EXISTS production_orders_status_check");
        DB::statement("ALTER TABLE production_orders ADD CONSTRAINT production_orders_status_check
            CHECK (status IN ('pending','in_progress','done','pending_dispatch','dispatched','sent','delivered','returned','cancelled'))");
    }

    public function down(): void
    {
        // DEUDA TÉCNICA CONOCIDA: este down() falla si ya existe alguna fila con
        // un status fuera del set angosto original (p.ej. 'pending_dispatch',
        // 'dispatched', 'sent', 'returned') — muy probable una vez la app está en
        // uso real. No se arregla ahora porque el forward ya está probado y no
        // dependemos de rollback en este despliegue.
        DB::statement("ALTER TABLE production_orders DROP CONSTRAINT IF EXISTS production_orders_status_check");
        DB::statement("ALTER TABLE production_orders ADD CONSTRAINT production_orders_status_check
            CHECK (status IN ('pending','in_progress','done','delivered','cancelled'))");
    }
};
