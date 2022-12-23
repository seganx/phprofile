<?php
const ack_filename = __FILE__ . '.meta';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../_configs.php';
require '../_league.php';
require '../_database.php';
require '../_time.php';

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
$context->time = calendar::get_now();
$context->is_end_of_day = calendar::is_end_of_day($context->time, false);
$context->is_end_of_week = calendar::is_end_of_week($context->time, false);
$context->is_end_of_month = calendar::is_end_of_month($context->time, false);

foreach ($context->leagues as $key => $item)
{
    // create list of 100 for each league
    $db->query("CALL league_{$item->name}_update(0, 100);");
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
        	file_put_contents(dirname(__FILE__) . '/../cache/leaderboard_' . $item->name . '_0_100.txt', json_encode($rows), LOCK_EX);
		}
		catch (Exception $e) 
		{
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
    }

	if ($context->curr_ack != $context->last_ack)
	{
		echo "checking league {$item->name} due to ack-number changed: ";
		$is_daily = $item->mode == league::mode_daily && $context->is_end_of_day;
		$is_weekly = $item->mode == league::mode_weekly && $context->is_end_of_week;
		$is_monthly = $item->mode == league::mode_monthly && $context->is_end_of_month;
		
		if ($is_daily || $is_weekly || $is_monthly)
		{
			$query = "SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league_{$item->name} l LEFT JOIN profile p on l.profile_id=p.id WHERE l.score>0 && l.rank>0 ORDER BY l.rank ASC LIMIT 3";
			echo "Performing {$query}...\n";

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
					
					$db->multi_query("UPDATE league_{$item->name} SET end_score=score, end_rank=rank;UPDATE league_{$item->name} SET rank=0, score={$item->base_score};");
					
					file_put_contents(ack_filename, $context->curr_ack);
				}
				catch (Exception $e) 
				{
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
				echo "league_{$item->name} updated: {$db->affected_rows()} rows affected!\n";
			}
		}
		else 
		{
			echo "not performed.\n";
			file_put_contents(ack_filename, $context->curr_ack);
		}

		
		echo "context:";
		echo json_encode($context);
		echo "\n";
	}
}
$db->close();

?>