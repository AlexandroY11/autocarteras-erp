<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('consecutive')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('color');
            $table->boolean('sticker')->default(false);
            $table->string('sticker_color')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('advance_payment', 10, 2)->default(30000);
            $table->date('due_date');
            $table->foreignId('current_stage_id')
                  ->nullable()
                  ->constrained('stages')
                  ->nullOnDelete();
            $table->enum('status', [
                'pending',
                'in_progress',
                'done',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};