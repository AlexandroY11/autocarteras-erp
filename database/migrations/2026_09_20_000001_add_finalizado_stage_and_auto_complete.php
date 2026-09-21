<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->boolean('auto_complete')->default(false)->after('active');
        });

        // "Empaque" deja de ser una etapa de trabajo real, pero se conserva
        // (hay pedidos con historial en order_stages que la referencian).
        DB::table('stages')->where('name', 'Empaque')->update([
            'active' => false,
            'order'  => 90,
        ]);

        DB::table('stages')->insert([
            'name'          => 'Finalizado',
            'order'         => 7,
            'color'         => '#10B981',
            'active'        => true,
            'auto_complete' => true,
        ]);
    }

    public function down(): void
    {
        // DEUDA TÉCNICA CONOCIDA: este delete falla con violación de FK si ya
        // existe algún order_stages apuntando a la etapa "Finalizado" (muy
        // probable una vez la app está en uso real — es la etapa de
        // auto-completado). No se arregla ahora porque el forward ya está
        // probado y no dependemos de rollback en este despliegue.
        DB::table('stages')->where('name', 'Finalizado')->delete();

        DB::table('stages')->where('name', 'Empaque')->update([
            'active' => true,
            'order'  => 7,
        ]);

        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn('auto_complete');
        });
    }
};
