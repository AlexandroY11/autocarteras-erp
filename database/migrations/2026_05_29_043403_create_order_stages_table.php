<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('stage_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stages');
    }
};