<?php

class sxerror
{
    const server_maintenance = "server_maintenance";
    const invalid_token = "invalid_token";
    const invalid_params = "invalid_params";
    const account_transfered = "account_transfered";
}

class league
{
    const mode_daily = "daily";
    const mode_weekly = "weekly";
    const mode_monthly = "monthly";
    
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

    public function is_ended()
    {
	    $time = time();
        $totalDays = (int)round($time / 86400) - 91;
        $totalYears = (int)round($totalDays / 365);
        $days = $totalDays % 365;
	    $day = ($days < 190) ? ($days % 31) : ($days - 186) % 30;
    
        if (this->$mode == "daily")
    	    return true;
        else if (this->$mode == "weekly")
    	    return ($days + 1) % 7 == 0;
        else if (this->$mode == "monthly")
    	    return $day == 0;
    }
}

?>