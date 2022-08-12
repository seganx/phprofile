<?php

require '_classes.php';
require '_database.php';
require '_configs.php';

$db = database::connect();
if ($db == null) 
    exit();

$all = get_all_leagues();

// reset all positions in all leagues
$db->query("UPDATE league SET rank=0");
    
// compute positions for each league
foreach ($all as $key => $item)
{
    $db->multi_query("SET @r=0;UPDATE league SET rank=@r:=(@r+1) WHERE league_id=$item->id ORDER BY score DESC LIMIT 100000;");

    // create list of 100 for each league
    $rows = array();
    $db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league l LEFT JOIN profile p on l.profile_id=p.id WHERE l.league_id=$item->id && l.rank>=0 ORDER BY l.rank ASC LIMIT 100");
    while($r = $db->result->fetch_assoc())
    {
        $rows[] = $r;
    }

    // save the list to a file as cache
    file_put_contents(dirname(__FILE__) . '/cache/leaderboard_' . $item->id . '_0_100.txt', json_encode($rows), FILE_TEXT | LOCK_EX);
}
$db->close();

?>