<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'year'];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
    ];

    public static function isHoliday($date)
    {
        $dateStr = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return self::where('date', $dateStr)->exists();
    }

    public static function getHolidayDates($year = null)
    {
        $query = self::select('date');
        
        if ($year) {
            $query->where('year', $year);
        }
        
        return $query->get()->map(fn($h) => $h->date->format('Y-m-d'))->toArray();
    }
}