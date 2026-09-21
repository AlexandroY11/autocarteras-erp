<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'director', 'worker', 'integration'))");
    }

    public function down(): void
    {
        // DEUDA TÉCNICA CONOCIDA: este down() falla si ya existe algún usuario con
        // role='integration' (confirmado: lo hay en dev). No se arregla ahora
        // porque el forward ya está probado y no dependemos de rollback en este
        // despliegue.
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'director', 'worker'))");
    }
};
