<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Quién vendió, dato independiente de created_by (quién registró
            // el pedido en el sistema — para el bot, siempre el usuario
            // técnico de integración). Nullable: los históricos quedan en
            // NULL, que significa "no definido", no "sin vendedor".
            $table->foreignId('seller_id')->nullable()->after('created_by');

            // Postgres no indexa automáticamente la columna que referencia
            // una FK (solo la PK referenciada), así que el índice va
            // explícito o no existe.
            $table->index('seller_id');

            // restrictOnDelete y no nullOnDelete: si algún día se fuerza el
            // borrado real de un usuario, preferimos que falle a perder el
            // historial de quién vendió. En la práctica casi nunca se
            // dispara, porque User usa SoftDeletes y un borrado normal no
            // toca la FK.
            $table->foreign('seller_id')
                  ->references('id')
                  ->on('users')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropIndex(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};
