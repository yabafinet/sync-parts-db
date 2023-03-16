<?php

namespace App\Services\Helpers;

use Illuminate\Support\Carbon;

class DateTimeHelper
{
    public static function diffInSeconds($start_time, $finish_time = null)
    {
        $startTime = Carbon::parse($start_time);
        $finishTime = Carbon::parse($finish_time ?? Carbon::now()->toDateTimeString());

        return $finishTime->diffInSeconds($startTime);
    }
}