<?php
const ack_filename = __FILE__ . '.meta';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../_configs.php';
require '../_league.php';
require '../_database.php';
require '../_calendar.php';

$db = database::connect();
if ($db == null)
{
	sleep(10);
	$db = database::connect();
}
if ($db == null)
{
	sleep(10);
	$db = database::connect();
}
if ($db == null)
{
	echo "job exited unexpectedly du to database connection failed!\n";
    exit();
}

$context = new stdclass();
$context->curr_ack = date('y:m:d:H');
$context->last_ack = file_get_contents(ack_filename);
$context->leagues = league::get_all_leagues();
$context->time = calendar::get_now_int();

$cache_dir = dirname(__FILE__) . '/../cache';

foreach ($context->leagues as $key => $item)
{
    $item->update_ranks($db);
    $item->create_leaderboard_overall($db, $cache_dir);
    $item->create_leaderboard($db, $cache_dir, 0, 1500);
    $item->create_leaderboard($db, $cache_dir, 1500, 2500);
    $item->create_leaderboard($db, $cache_dir, 2500, 4000);
    $item->create_leaderboard($db, $cache_dir, 4000, 6000);
    $item->create_leaderboard($db, $cache_dir, 0, 999999); // should start with 6000 but it also should be used top100 in start of the league

    if ($context->curr_ack != $context->last_ack)
	{
		$is_end_of_league = calendar::is_end_of_period($context->time, $item->start_time, $item->duration, 3000);
		
		if ($is_end_of_league)
		{
			//echo "checking league {$item->name} due to ack-number changed. is_end_of_league:True\r\n";

			$query = "SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league_{$item->name} l LEFT JOIN profile p on l.profile_id=p.id WHERE l.score>0 && l.rank>0 ORDER BY l.rank ASC LIMIT 3";
			//echo "Performing {$query}...\r\n";

            $db->query($query);
            if ($db->has_result())
            {
                $rows = array();
                while($r = $db->result->fetch_assoc())
                {
                    $rows[] = $r;
                }
				
                try
                {
                    file_put_contents(dirname(__FILE__) . '/../cache/leaderboard_' . $item->name . '_last_3.txt', json_encode($rows), LOCK_EX);

    			    $query = "UPDATE league_{$item->name} SET end_score=score, end_rank=rank, score={$item->base_score}, rank=0";
                    $db->query($query);
				    	
                    file_put_contents(ack_filename, $context->curr_ack);
                }
                catch (Exception $e) 
                {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
				    echo "Performing {$query}...\r\n";
                }
                //echo "league_{$item->name} updated: {$db->affected_rows()} rows affected!\r\n";
            }
		}
		else 
		{
			//echo "checking league {$item->name} due to ack-number changed. is_end_of_league:False\r\n";
			//echo "not performed.\r\n";
			file_put_contents(ack_filename, $context->curr_ack);
		}

		//echo "context:";
		//echo json_encode($context);
		//echo "\r\n\r\n";
	}
}
$db->close();

?>
