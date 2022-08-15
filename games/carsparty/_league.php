<?php
class league
{
    public const mode_daily = 'daily';
    public const mode_weekly = 'weekly';
    public const mode_monthly = 'monthly';

    public $id = 0;
    public $base_score = 1000;
    public $max_value = 30;
    public $mode = '';

    function __construct ($id, $base_score, $max_value, $mode)
    {
        $this->id = $id;
        $this->base_score = $base_score;
        $this->max_value = $max_value;
        $this->mode = $mode;
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

        $res[1] = new league(1, 0, 15, league::mode_weekly);
        $res[2] = new league(2, 0, 15, league::mode_weekly);
        $res[3] = new league(3, 0, 15, league::mode_weekly);
        $res[4] = new league(4, 0, 15, league::mode_weekly);

        return $res;
    }
}
?>