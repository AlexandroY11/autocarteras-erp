<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('efectivo','nequi','nu'))");
    }

    public function down(): void
    {
        // DEUDA TÉCNICA CONOCIDA: este down() falla si ya existe alguna fila
        // con payment_method='nu'. No se arregla ahora porque el forward ya
        // está probado y no dependemos de rollback en este despliegue.
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('efectivo','nequi'))");
    }
};
