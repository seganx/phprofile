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

    // create list of 100 for each league
    $rows = array();
    $db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league_{$item->name} l LEFT JOIN profile p on l.profile_id=p.id WHERE l.score>{$item->base_score} && l.rank>0 ORDER BY l.rank ASC LIMIT 100");
    while($r = $db->result->fetch_assoc())
    {
        $rows[] = $r;
    }

    // save the list to a file as cache
    file_put_contents(dirname(__FILE__) . '/cache/leaderboard_' . $item->name . '_0_100.txt', json_encode($rows), FILE_TEXT | LOCK_EX);
}
$db->close();

?>