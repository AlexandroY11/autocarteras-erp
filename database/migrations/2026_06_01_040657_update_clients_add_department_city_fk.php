<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Agregar nuevas columnas FK
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->nullOnDelete();
            $table->foreignId('city_id')
                  ->nullable()
                  ->constrained('cities')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['department_id', 'city_id']);
        });
    }
};