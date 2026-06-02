// database/migrations/2024_01_01_000001_create_holidays_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->integer('year');
            $table->timestamps();
        });
        
        Schema::create('holiday_days', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->json('dates'); // Array de fechas [MM-DD]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_days');
        Schema::dropIfExists('holidays');
    }
};