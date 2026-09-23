<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal — contraseña aleatoria por corrida, impresa una
        // sola vez en consola (nunca hardcodeada: un seeder con contraseña
        // fija y predecible es una credencial de facto si esto llega a
        // correr fuera de un entorno 100% descartable).
        $adminPassword = Str::password(16);
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@autocarteras.com',
            'phone' => '3170000000',
            'password' => $adminPassword,
            'role' => 'admin',
            'active' => true,
        ]);
        $this->command?->info("Admin creado: admin@autocarteras.com / {$adminPassword}");

        // Trabajadores de prueba
        $workers = [
            ['name' => 'Pedro Ramírez',  'email' => 'pedro@autocarteras.com',  'phone' => '3171111111'],
            ['name' => 'Juan Mosquera',  'email' => 'juan@autocarteras.com',   'phone' => '3172222222'],
            ['name' => 'Carlos López',   'email' => 'carlos@autocarteras.com', 'phone' => '3173333333'],
        ];

        foreach ($workers as $worker) {
            $workerPassword = Str::password(16);
            User::create([
                ...$worker,
                'password' => $workerPassword,
                'role' => 'worker',
                'active' => true,
            ]);
            $this->command?->info("Worker creado: {$worker['email']} / {$workerPassword}");
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
