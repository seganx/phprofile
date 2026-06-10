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

    public function update_ranks($db)
    {
        $db->multi_query("UPDATE `league_{$this->name}` SET `rank`=0; SET @r=0; UPDATE `league_{$this->name}` SET `rank`=@r:=(@r+1) WHERE `score`>0 ORDER BY `score` DESC LIMIT 100000;");
    }

    public function create_leaderboard_overall($db, string $directory)
    { 
        $db->query("SELECT `p`.`username`, `p`.`nickname`, `p`.`avatar`, `l`.`total_score` as `score` FROM `league_{$this->name}` `l` LEFT JOIN `profile` `p` ON `l`.`profile_id`=`p`.`id` WHERE `l`.`total_score`>1000 ORDER BY `l`.`total_score` DESC LIMIT 100");
        $this->save_leaderboard($db, "{$directory}/leaderboard_{$this->name}_overall.txt");
    }

    public function create_leaderboard($db, string $directory, int $min_score, int $max_score)
    { 
        $db->query("SELECT `p`.`username`, `p`.`nickname`, `p`.`avatar`, `l`.`score`, `l`.`rank` FROM `profile` `p` LEFT JOIN `league_{$this->name}` `l` ON `l`.`profile_id`=`p`.`id` WHERE `l`.`score`>={$min_score} && `l`.`score`<{$max_score} && `l`.`rank`>0 ORDER BY `l`.`rank` ASC LIMIT 100");
        $this->save_leaderboard($db, "{$directory}/leaderboard_{$this->name}_{$min_score}_{$max_score}.txt");
    }

    private function save_leaderboard($db, string $filename)
    {
        if ($db->has_result())
        {
            $rows = array();
            while($r = $db->result->fetch_assoc())
            {
                $rows[] = $r;
            }
        
            // save the list to a file as cache
            try
            {			
                file_put_contents($filename, json_encode($rows), LOCK_EX);
            }
            catch (Exception $e) 
            {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }
    }

    public static function get_all_leagues()
    {
        $res = array();

        $res['total'] = new league('total', 1000, 40, 1671840000, 2419200);

        return $res;
    }
}
?>
