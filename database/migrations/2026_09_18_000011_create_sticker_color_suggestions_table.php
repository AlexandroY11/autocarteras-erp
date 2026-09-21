<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sticker_color_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        DB::table('sticker_color_suggestions')->insert(collect([
            'Rojo', 'Azul', 'Amarillo', 'Verde', 'Naranja', 'Morado', 'Rosa',
            'Café', 'Gris', 'Negro', 'Blanco', 'Tornasol', 'Dorado', 'Plateado',
        ])->map(fn ($name) => [
            'name' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('sticker_color_suggestions');
    }
};
