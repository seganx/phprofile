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
        $totalDays = (int)round($time / 86400) - 91;
        $days = $totalDays % 365;
	    $day = ($days < 190) ? ($days % 31) : ($days - 186) % 30;

        switch ($this->mode)
        {
            case self::mode_weekly: return (($days + 1) % 7);
            case self::mode_monthly: return $day;
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

        $res['total'] = new league('total', league::mode_weekly, 0, 15);

        return $res;
    }
}
?>