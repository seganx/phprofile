<?php
class league
{
    public const mode_daily = "daily";
    public const mode_weekly = "weekly";
    public const mode_monthly = "monthly";
    
    public $id = 0;
    public $base_score = 1000;
    public $max_value = 30;
    public $mode = "";
    
    function __construct ($id, $base_score, $max_value, $mode)
    {
        $this->id = $id;
        $this->base_score = $base_score;
        $this->max_value = $max_value;
        $this->mode = $mode;
    }

    public function days_left()
    {
	    $time = time();
        $totalDays = (int)round($time / 86400) - 91;
        $totalYears = (int)round($totalDays / 365);
        $days = $totalDays % 365;
	    $day = ($days < 190) ? ($days % 31) : ($days - 186) % 30;
    
        if ($this->$mode == self::mode_daily)
    	    return 0;
        else if ($this->$mode == self::mode_weekly)
    	    return (($days + 1) % 7);
        else if ($this->$mode == self::mode_monthly)
    	    return $day;
    }
	
	public function is_ended()
    {
	    return $this->days_left() == 0;
    }
}
?>