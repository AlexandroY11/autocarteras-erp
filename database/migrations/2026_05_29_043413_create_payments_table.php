<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['advance', 'partial', 'final']);
            $table->text('notes')->nullable();
            $table->date('paid_at');
            $table->foreignId('registered_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};