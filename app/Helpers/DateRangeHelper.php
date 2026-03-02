<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateRangeHelper
{
    /**
     * return array [start, end, prevStart, prevEnd]
     */
    public static function parse(Request $request): array
    {
        $period = $request->query('period', 'this_month');

        switch ($period) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today()->endOfDay();
                $duration = 1;
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $duration = 7;
                break;
            case 'custom':
                $start = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
                $end = Carbon::parse($request->query('end_date', Carbon::today()->toDateString()))->endOfDay();
                $duration = $start->diffInDays($end) + 1;
                break;
            case 'this_month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfDay();
                $duration = $start->diffInDays($end) + 1;
                break;
        }

        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($duration - 1)->startOfDay();

        return [$start, $end, $prevStart, $prevEnd];
    }
}
