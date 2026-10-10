<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_dispatches', function (Blueprint $table) {
            // Constancia de que esta guía salió con el envío sin pagar, y por
            // cuánto. Antes era imposible: el despacho estaba bloqueado si
            // quedaba saldo de envío, así que ninguna fila existente puede
            // haber salido en esa condición — el default false es correcto
            // para todo el historial, no una suposición.
            //
            // Quién lo confirmó no necesita columna propia: la confirmación y
            // el despacho son la misma acción, en el mismo request, del mismo
            // Admin que ya queda en dispatched_by.
            $table->boolean('shipped_with_pending_shipping')
                  ->default(false)
                  ->after('collected_amount');

            // Snapshot del saldo de envío en el momento del despacho — no se
            // recalcula después, para que quede qué se debía ese día aunque
            // el saldo cambie luego.
            $table->decimal('pending_shipping_amount', 10, 2)
                  ->nullable()
                  ->after('shipped_with_pending_shipping');
        });
    }

    public function down(): void
    {
        Schema::table('order_dispatches', function (Blueprint $table) {
            $table->dropColumn(['shipped_with_pending_shipping', 'pending_shipping_amount']);
        });
    }
};
