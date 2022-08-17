<?php

require '_configs.php';
require '_league.php';
require '_database.php';

$db = database::connect();
if ($db == null)
    exit();

$all = league::get_all_leagues();


// compute ranks for each league
foreach ($all as $key => $item)
{
    // reset all ranks
    $db->query("UPDATE league_{$item->name} SET rank=0");

    // set ranks base on scores
    $db->multi_query("SET @r=0; UPDATE league_{$item->name} SET rank=@r:=(@r+1) WHERE score>{$item->base_score} ORDER BY score DESC LIMIT 100000;");
}

// update end for all leagues
foreach ($all as $key => $item)
{
    if ($item->is_ended())
    {
        // create list of 3 legends
        $rows = array();
        $db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league_{$item->name} l LEFT JOIN profile p on l.profile_id=p.id WHERE l.score>{$item->base_score} && l.rank>0 ORDER BY l.rank ASC LIMIT 3");
        while($r = $db->result->fetch_assoc())
        {
            $rows[] = $r;
        }
        // save the list to a file as cache
        file_put_contents(dirname(__FILE__) . '/cache/leaderboard_' . $item->name . '_last_3.txt', json_encode($rows), FILE_TEXT | LOCK_EX);

        $db->multi_query("UPDATE league_{$item->name} SET end_score=score, end_rank=rank;UPDATE league_{$item->name} SET rank=0, score={$item->base_score};");
    }
}

$db->close();

?>