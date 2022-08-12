<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

$db = database::connect();
if ($db == null) 
    exit();

$all = get_all_leagues();


// reset all ranks in all leagues
$db->query("UPDATE league SET rank=0");
    
// compute ranks for each league
foreach ($all as $key => $item)
    $db->multi_query("SET @r=0;UPDATE league SET rank=@r:=(@r+1) WHERE league_id=$item->id ORDER BY score DESC LIMIT 100000;");

// update end for all leagues
foreach ($all as $key => $item)
{
    if ($item->is_ended())
    {        
        // create list of 3 legends
        $rows = array();
        $db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league l LEFT JOIN profile p on l.profile_id=p.id WHERE l.league_id=$item->id && l.rank>=0 ORDER BY l.rank ASC LIMIT 3");
        while($r = $db->result->fetch_assoc())
        {
            $rows[] = $r;
        }
        // save the list to a file as cache
        file_put_contents(dirname(__FILE__) . '/cache/leaderboard_' . $item->id . '_last_3.txt', json_encode($rows), FILE_TEXT | LOCK_EX);

        $db->multi_query("UPDATE league SET end_score=score, end_rank=rank WHERE league_id=$item->id;UPDATE league SET rank=0, score=$item->base_score WHERE league_id=$item->id;");
    }
}

$db->close();

?>