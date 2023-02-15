<?php

class calendar
{
    //public const offset = 7936201;

    public static function get_now_int() : int
    { 
        return time() + 12600;// - calendar::offset; 
    }

    //public static function get_now_by_time(int $time)
    //{
    //    $total_days = (int)floor($time / 86400);
    //    $day_of_year = $total_days % 365;

    //    // compute date
    //    $is_first_half = ($day_of_year < 187);
    //    $month_of_year = (int)floor($is_first_half ? ($day_of_year / 31) : (6 + ($day_of_year - 186) / 30));
    //    $day_of_month = $is_first_half ? ($day_of_year % 31) : ($day_of_year - 186) % 30;
    //    $day_of_week = (($day_of_year + 2) % 7);

    //    // compute time
    //    $seconds_of_day = $time % 86400;
    //    $hours_of_day = (int)floor($seconds_of_day / 3600);
    //    $seconds_of_hour = $time % 3600;
    //    $minutes_of_hour = (int)floor($seconds_of_hour / 60);
    //    $seconds_of_minut = $time % 60;

    //    $result = new stdClass();
    //    $result->time = $time;
    //    $result->month = (int)$month_of_year;
    //    $result->day = (int)$day_of_month;
    //    $result->day_of_week = (int)$day_of_week;
    //    $result->hour = (int)$hours_of_day;
    //    $result->minute = (int)$minutes_of_hour;
    //    $result->second = (int)$seconds_of_minut;
    //    return $result;
    //}

    //public static function get_now()
    //{
    //    return calendar::get_now_by_time(calendar::get_now_int());
    //}

	public static function translate(int $time)
	{
		// compute time
		$total_days = (int)floor($time / 86400);
        $seconds_of_day = $time % 86400;
        $hours_of_day = (int)floor($seconds_of_day / 3600);
        $seconds_of_hour = $time % 3600;
        $minutes_of_hour = (int)floor($seconds_of_hour / 60);
        $seconds_of_minut = $time % 60;

		$result = new stdClass();
        $result->time = $time;
        $result->days = (int)$total_days;
        $result->hours = (int)$hours_of_day;
        $result->minute = (int)$minutes_of_hour;
        $result->second = (int)$seconds_of_minut;
        return $result;
	}

    public static function is_end_of_period(int $time, int $start_time, int $duration, int $error_range) : bool
    {
	    $delta = $time - $start_time;
        $spent = $delta % $duration;
        $result = $spent < $error_range;
        //echo "time:{$time} startTime:{$start_time} duration:{$duration} errorRange:{$error_range} | delta:{$delta} spent:{$spent} result:{$result} </br>";

		$tmp = calendar::translate($duration - $spent);
		echo 'Remained time: ' . json_encode($tmp) . "</br>";

        return $result;
    }    
}


?>
