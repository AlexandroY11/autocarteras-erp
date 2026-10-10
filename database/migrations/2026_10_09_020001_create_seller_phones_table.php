<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teléfonos desde los que un vendedor le escribe al bot (el remitente
        // en Chatwoot). Tabla aparte y no users.phone porque un vendedor
        // puede tener varios números, y porque users.phone es un dato de
        // contacto que un Admin edita libremente desde el panel: mezclarlos
        // haría que cambiarlo ahí rompiera la resolución del bot sin que
        // nadie se entere.
        Schema::create('seller_phones', function (Blueprint $table) {
            $table->id();

            // Normalizado a solo dígitos por SellerPhone::normalize(), que es
            // la misma función que usa la resolución del bot. Unique: un
            // número no puede apuntar a dos vendedores.
            $table->string('phone')->unique();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_phones');
    }
};
