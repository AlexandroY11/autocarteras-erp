<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('name');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('first_name');
            $table->index('phone');
            $table->index('active');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('current_stage_id');
            $table->index('consecutive');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['name']);
        });
    }
};