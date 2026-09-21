<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('production_orders')
            ->where('advance_payment', '>', 0)
            ->orderBy('id')
            ->each(function ($order) {
                DB::table('payments')->insert([
                    'production_order_id' => $order->id,
                    'amount' => $order->advance_payment,
                    'type' => 'advance',
                    'payment_method' => 'efectivo',
                    'notes' => 'Anticipo migrado automáticamente',
                    'paid_at' => $order->created_at,
                    'registered_by' => $order->created_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('payments')->where('notes', 'Anticipo migrado automáticamente')->delete();
    }
};
