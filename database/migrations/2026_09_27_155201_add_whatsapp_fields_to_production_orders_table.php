<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Clave de idempotencia del bot de WhatsApp/n8n — nullable porque
            // los pedidos creados manualmente en el ERP nunca la traen. La
            // constraint unique es la protección real contra condiciones de
            // carrera (dos llamadas casi simultáneas con la misma key): la
            // segunda falla en el INSERT, no en una lectura previa.
            $table->string('idempotency_key')->nullable()->unique()->after('created_by');

            // string, no enum nativo de Postgres — mismo patrón que ya usan
            // status/dispatch_status en este proyecto (validación de
            // aplicación, no CHECK constraint de BD).
            $table->string('origin')->default('manual')->after('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn(['idempotency_key', 'origin']);
        });
    }
};
