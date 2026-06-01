<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $blueprint) {
            // Eliminamos las columnas que causan el conflicto
            $blueprint->dropColumn(['department', 'city']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $blueprint) {
            // Por si necesitas revertir, las volvemos a crear como string
            $blueprint->string('department')->nullable();
            $blueprint->string('city')->nullable();
        });
    }
};
