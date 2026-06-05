<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ColombiaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Descargando departamentos y ciudades de Colombia...');

        // Obtenemos todos los departamentos con reintentos
        $response = Http::timeout(30)->retry(3, 2000)->get('https://api-colombia.com/api/v1/Department');

        if (!$response->ok()) {
            $this->command->error('No se pudo conectar a la API de departamentos.');
            return;
        }

        $departments = $response->json();

        // Identificar IDs especiales
        $bogotaApiId = null;
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
            // Saltar Bogotá D.C. como departamento (se agregará a Cundinamarca)
            if ($dept['id'] === $bogotaApiId) {
                continue;
            }

            try {
                $department = Department::firstOrCreate(['name' => $dept['name']]);

                // Pausa de 0.8 segundos entre peticiones para no saturar la API (Rate Limiting)
                usleep(800000); 

                $citiesResponse = Http::timeout(30)
                    ->retry(3, 2000) // Reintenta 3 veces, esperando 2 segundos entre fallos
                    ->get("https://api-colombia.com/api/v1/Department/{$dept['id']}/cities");

                if (!$citiesResponse->ok()) {
                    $this->command->warn("  ! No se pudieron obtener ciudades para {$dept['name']}");
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

            } catch (\Exception $e) {
                $this->command->error("Error procesando {$dept['name']}: " . $e->getMessage());
                // Continuamos con el siguiente departamento a pesar del error
                continue;
            }
        }

        $this->command->info('¡Listo! Proceso de carga finalizado.');
    }
}
