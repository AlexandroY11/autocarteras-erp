<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use Carbon\Carbon;

class SyncColombiaHolidays extends Command
{
    protected $signature = 'holidays:colombia {--year= : Año para sincronizar (por defecto: año actual)}';
    protected $description = 'Sincronizar festivos de Colombia en la base de datos';

    public function handle()
    {
        $year = $this->option('year') ?: now()->year;
        
        // Festivos Fijos (siempre en la misma fecha)
        $fixedHolidays = [
            '01-01' => 'Año Nuevo',
            '05-01' => 'Día del Trabajo',
            '07-20' => 'Declaración de Independencia',
            '08-07' => 'Batalla de Boyacá',
            '12-25' => 'Navidad',
        ];

        // Festivos Móviles (dependen de Pascua)
        $easter = $this->getEasterDate($year);
        
        $movableHolidays = [
            $easter->subDays(2)->format('m-d') => 'Viernes Santo',
            $easter->addDays(5)->format('m-d') => 'Jueves Santo',
            Carbon::parse($easter)->addDays(39)->format('m-d') => 'Ascensión del Señor',
            Carbon::parse($easter)->addDays(49)->format('m-d') => 'Corpus Christi',
            Carbon::parse($easter)->addDays(56)->format('m-d') => 'Sagrado Corazón',
        ];

        // Festivos que se mueven al lunes siguiente (Lunes de Feria)
        $lunnyHolidays = [
            '01-06' => 'Día de Reyes',
            '03-19' => 'San José',
            $easter->addDays(60)->format('m-d') => 'San Pedro y San Pablo',
            '08-15' => 'Asunción de la Virgen',
            '10-12' => 'Día de la Raza',
            '11-01' => 'Todos los Santos',
            '11-11' => 'Independencia de Cartagena',
            '12-08' => 'Inmaculada Concepción',
        ];

        $allHolidays = [];

        // Festivos fijos
        foreach ($fixedHolidays as $date => $name) {
            $allHolidays["{$year}-{$date}"] = $name;
        }

        // Festivos móviles
        foreach ($movableHolidays as $date => $name) {
            $allHolidays["{$year}-{$date}"] = $name;
        }

        // Festivos que se mueven al lunes
        foreach ($lunnyHolidays as $date => $name) {
            $holidayDate = Carbon::parse("{$year}-{$date}");
            
            // Si cae en domingo, se mueve al lunes
            if ($holidayDate->dayOfWeek === Carbon::SUNDAY) {
                $holidayDate->addDay();
            }
            
            $allHolidays[$holidayDate->format('Y-m-d')] = $name;
        }

        // Guardar en la base de datos
        $created = 0;
        foreach ($allHolidays as $date => $name) {
            Holiday::updateOrCreate(
                ['date' => $date],
                ['name' => $name, 'year' => $year]
            );
            $created++;
        }

        $this->info("✅ Se sincronizaron {$created} festivos para {$year}");
        
        // Mostrar próximos 5 festivos
        $upcoming = Holiday::where('date', '>=', now())
            ->orderBy('date')
            ->limit(5)
            ->get();
        
        if ($upcoming->count() > 0) {
            $this->newLine();
            $this->info('Próximos festivos:');
            foreach ($upcoming as $holiday) {
                $this->line("  • {$holiday->date->format('d/m/Y')} - {$holiday->name}");
            }
        }
    }

    private function getEasterDate($year)
    {
        // Algoritmo de Gauss para calcular la fecha de Pascua
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return Carbon::create($year, $month, $day);
    }
}