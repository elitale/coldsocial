<?php

namespace App\Content;

use Illuminate\Support\Carbon;

final class ScheduleTime
{
    /**
     * Parse a datetime-local string in the user's timezone to a UTC Carbon,
     * or null when the moment is in the past.
     */
    public static function fromUserInput(string $input, string $timezone): ?Carbon
    {
        $at = Carbon::parse($input, $timezone)->utc();

        return $at->isPast() ? null : $at;
    }
}
