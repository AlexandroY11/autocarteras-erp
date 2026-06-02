<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SyncColombiaHolidays extends Command
{
    protected $signature = 'holidays:colombia {--year= : Año para sincronizar (por defecto: año actual)}';
    protected $description = 'Sincronizar festivos de Colombia usando API externa';

    public function handle()
    {
        $year = $this->option('year') ?: now()->year;
        
        $this->info("Buscando festivos para Colombia en el año {$year}...");

        // Consultamos la API oficial de Nager.Date para Colombia (CO)
        $response = Http::get("https://date.nager.at/api/v3/PublicHolidays/{$year}/CO" );

        if ($response->failed()) {
            $this->error("❌ No se pudo conectar con la API de festivos.");
            return 1;
        }

        $holidays = $response->json();
        $count = 0;

        foreach ($holidays as $holiday) {
            // Guardamos o actualizamos en la tabla
            Holiday::updateOrCreate(
                ['date' => $holiday['date']], // Buscamos por fecha
                [
                    'name' => $holiday['localName'], // Nombre en español (o local)
                    'year' => $year
                ]
            );
            $count++;
        }

        $this->info("✅ ¡Éxito! Se han sincronizado {$count} festivos para el año {$year}.");

        // Mostrar un resumen en la consola
        $this->table(
            ['Fecha', 'Nombre'],
            Holiday::where('year', $year)->orderBy('date')->get(['date', 'name'])->toArray()
        );
    }
}
