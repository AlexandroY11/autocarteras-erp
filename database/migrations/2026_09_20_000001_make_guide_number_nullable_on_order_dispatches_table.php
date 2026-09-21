<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El swipe para despachar (pending_dispatch -> dispatched) ya no exige
     * capturar la guía en el mismo paso — ahora es un paso independiente
     * (DispatchService::setGuideNumber), asignable en cualquier momento.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE order_dispatches ALTER COLUMN guide_number DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE order_dispatches SET guide_number = 'SIN-GUIA' WHERE guide_number IS NULL");
        DB::statement('ALTER TABLE order_dispatches ALTER COLUMN guide_number SET NOT NULL');
    }
};
