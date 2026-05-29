<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@autocarteras.com',
            'phone' => '3170000000',
            'password' => 'admin123',
            'role' => 'admin',
            'active' => true,
        ]);

        // Trabajadores de prueba
        $workers = [
            ['name' => 'Pedro Ramírez',  'email' => 'pedro@autocarteras.com',  'phone' => '3171111111'],
            ['name' => 'Juan Mosquera',  'email' => 'juan@autocarteras.com',   'phone' => '3172222222'],
            ['name' => 'Carlos López',   'email' => 'carlos@autocarteras.com', 'phone' => '3173333333'],
        ];

        foreach ($workers as $worker) {
            User::create([
                ...$worker,
                'password' => 'worker123',
                'role' => 'worker',
                'active' => true,
            ]);
        }

        $stages = [
            ['name' => 'Pendiente',  'order' => 1, 'color' => '#6B7280'],
            ['name' => 'Moldeado',   'order' => 2, 'color' => '#3B82F6'],
            ['name' => 'Vaciado',    'order' => 3, 'color' => '#8B5CF6'],
            ['name' => 'Lijado',     'order' => 4, 'color' => '#F59E0B'],
            ['name' => 'Pintura',    'order' => 5, 'color' => '#EF4444'],
            ['name' => 'Acabado',    'order' => 6, 'color' => '#EC4899'],
            ['name' => 'Empaque',    'order' => 7, 'color' => '#14B8A6'],
            ['name' => 'Enviado',    'order' => 8, 'color' => '#22C55E'],
        ];

        foreach ($stages as $stage) {
            Stage::create([...$stage, 'active' => true]);
        }

        $this->call(ProductSeeder::class);
    }
}
