<?php
class league
{
    public const mode_daily = 'daily';
    public const mode_weekly = 'weekly';
    public const mode_monthly = 'monthly';

    public $name = '';
    public $mode = '';
    public $base_score = 1000;
    public $max_value = 30;

    function __construct ($name, $mode, $base_score, $max_value)
    {
        $this->name = $name;
        $this->mode = $mode;
        $this->base_score = $base_score;
        $this->max_value = $max_value;
    }

    public function days_left(): int
    {
        $time = time();
        $totalDays = (int)ceil($time / 86400) - 93;
        $ydays = $totalDays % 365;
        $mdays = ($ydays < 187) ? ($ydays % 31) : ($ydays - 186) % 30;
        $wdays = (($ydays + 2) % 7);

        $r_mdays = ($ydays < 187) ? (30 - $mdays) : (29 - $mdays);
        $r_wdays = 6 - $wdays;

        switch ($this->mode)
        {
            case self::mode_weekly: return $r_wdays;
            case self::mode_monthly: return $r_mdays;
        }

        return 0;
    }

	public function is_ended(): bool
    {
	    return $this->days_left() == 0;
    }

    public static function get_all_leagues()
    {
        $res = array();

        $res['total'] = new league('total', league::mode_weekly, 0, 20);

        return $res;
    }
}
?>