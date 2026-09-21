<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class BusinessDaysService
{
    /**
     * Calcula la fecha de compromiso:
     * 15 días hábiles desde mañana,
     * sin contar domingos ni festivos colombianos.
     */
    public function calculateDueDate(int $businessDays = 15, ?Carbon $from = null): Carbon
    {
        $current = ($from ?? Carbon::now())->copy()->startOfDay();

        // Cargar festivos del año actual y el siguiente (por si cruza año)
        $holidays = $this->getHolidays($current->year);
        if ($current->month >= 11) {
            $holidays = array_merge($holidays, $this->getHolidays($current->year + 1));
        }

        $added = 0;

        while ($added < $businessDays) {
            $current->addDay();

            $dayOfWeek = $current->dayOfWeek; // 0 = domingo
            $dateStr   = $current->toDateString(); // YYYY-MM-DD

            if ($dayOfWeek !== Carbon::SUNDAY && !in_array($dateStr, $holidays)) {
                $added++;
            }
        }

        return $current;
    }

    /**
     * Días hábiles entre hoy y $target (sin domingos ni festivos, mismo
     * criterio que calculateDueDate()). Positivo si $target es futuro,
     * negativo si ya pasó (días hábiles de atraso), cero si es hoy.
     */
    public function businessDaysUntil(Carbon $target, ?Carbon $from = null): int
    {
        $today = ($from ?? Carbon::now())->copy()->startOfDay();
        $target = $target->copy()->startOfDay();

        if ($target->equalTo($today)) {
            return 0;
        }

        $direction = $target->greaterThan($today) ? 1 : -1;
        [$start, $end] = $direction === 1 ? [$today, $target] : [$target, $today];

        $holidays = $this->getHolidays($start->year);
        if ($end->year !== $start->year) {
            $holidays = array_merge($holidays, $this->getHolidays($end->year));
        }

        $count = 0;
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $cursor->addDay();

            if ($cursor->dayOfWeek !== Carbon::SUNDAY && !in_array($cursor->toDateString(), $holidays)) {
                $count++;
            }
        }

        return $count * $direction;
    }

    private function getHolidays(int $year): array
    {
        return Holiday::whereYear('date', $year)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();
    }
}