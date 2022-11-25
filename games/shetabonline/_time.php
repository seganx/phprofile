<?php

class calendar
{
    public const offset = 7936201;

    public static function get_now()
    {
        $time = time() - calendar::offset;

        $total_days = (int)floor($time / 86400);
        $day_of_year = $total_days % 365;

        // compute date
        $is_first_half = ($day_of_year < 187);
        $month_of_year = (int)floor($is_first_half ? ($day_of_year / 31) : (6 + ($day_of_year - 186) / 30));
        $day_of_month = $is_first_half ? ($day_of_year % 31) : ($day_of_year - 186) % 30;
        $day_of_week = (($day_of_year + 2) % 7);

        // compute time
        $seconds_of_day = $time % 86400;
        $hours_of_day = (int)floor($seconds_of_day / 3600);
        $seconds_of_hour = $time % 3600;
        $minutes_of_hour = (int)floor($seconds_of_hour / 60);
        $seconds_of_minut = $time % 60;

        $result = new stdClass();
        $result->time = time();
        $result->month = (int)$month_of_year;
        $result->day = (int)$day_of_month;
        $result->day_of_week = (int)$day_of_week;
        $result->hour = (int)$hours_of_day;
        $result->minute = (int)$minutes_of_hour;
        $result->second = (int)$seconds_of_minut;
        return $result;
    }

    public static function is_end_of_day(stdClass $time, bool $use_minute = true) : bool
    {
		if ($use_minute)
        {
			return $time->hour == 0 && $time->minut == 0;
		}
		else
		{
			return $time->hour == 0;
		}		
    }

    public static function is_end_of_week(stdClass $time, bool $use_minute = true) : bool
    {
		if ($use_minute)
        {
        	return $time->day_of_week == 0 && $time->hour == 0 && $time->minut == 0;
		}
		else 
		{
			return $time->day_of_week == 0 && $time->hour == 0;
		}
    }

    public static function is_end_of_month(stdClass $time, bool $use_minute = true) : bool
    {
		if ($use_minute)
        {
        	return $time->day == 0 && $time->hour == 0 && $time->minut == 0;
		}
		else
		{
			return $time->day == 0 && $time->hour == 0;
		}
    }
}


?>