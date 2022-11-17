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

    public static function get_all_leagues()
    {
        $res = array();

        $res['total'] = new league('total', league::mode_weekly, 1000, 20);

        return $res;
    }
}
?>