<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ColombiaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Descargando departamentos y ciudades de Colombia...');

        $response = Http::timeout(30)->get('https://api-colombia.com/api/v1/Department');

        if (!$response->ok()) {
            $this->command->error('No se pudo conectar a la API.');

            return;
        }

        $departments = $response->json();

        // ID de Bogotá D.C. en la API para ignorarlo como departamento
        $bogotaApiId = null;

        // Primero encontrar Cundinamarca para agregarle Bogotá
        $cundinamarcaApiId = null;

        foreach ($departments as $dept) {
            if (strtolower($dept['name']) === 'cundinamarca') {
                $cundinamarcaApiId = $dept['id'];
            }
            if (str_contains(strtolower($dept['name']), 'bogot')) {
                $bogotaApiId = $dept['id'];
            }
        }

        foreach ($departments as $dept) {
            // Saltar Bogotá D.C. como departamento
            if ($dept['id'] === $bogotaApiId) {
                continue;
            }

            $department = Department::firstOrCreate(['name' => $dept['name']]);

            $citiesResponse = Http::timeout(30)
                ->get("https://api-colombia.com/api/v1/Department/{$dept['id']}/cities");

            if (!$citiesResponse->ok()) {
                continue;
            }

            $cities = $citiesResponse->json();

            foreach ($cities as $city) {
                City::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => $city['name'],
                ]);
            }

            // Si es Cundinamarca, agregar Bogotá manualmente
            if ($dept['id'] === $cundinamarcaApiId) {
                City::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => 'Bogotá',
                ]);
                $this->command->info('  + Bogotá agregada a Cundinamarca');
            }

            $this->command->info("✓ {$department->name}: ".count($cities).' ciudades');
        }

        $this->command->info('¡Listo! Colombia cargada correctamente.');
    }
}
