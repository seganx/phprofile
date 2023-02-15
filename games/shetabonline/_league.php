<?php
class league
{
    public $name = '';
    public $base_score = 1000;
    public $max_value = 30;
    public $start_time = 1676073600;
    public $duration = 86400;

    function __construct ($name, $base_score, $max_value, $start_time, $duration)
    {
        $this->name = $name;
        $this->base_score = $base_score;
        $this->max_value = $max_value;
        $this->start_time = $start_time;
        $this->duration = $duration;
    }

    public static function get_all_leagues()
    {
        $res = array();

        $res['total'] = new league('total', 1000, 40, 1671840000, 2419200);

        return $res;
    }
}
?>
