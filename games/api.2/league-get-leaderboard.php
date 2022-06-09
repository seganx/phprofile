<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}
$userdata = get_post_json();

// validate league id
$league = get_all_leagues()[$userdata->id];
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

$userdata->from = intval($userdata->from);
$userdata->count = clamp($userdata->count, 5, 100);
$res = new stdClass();

// check to see if top players requested
if ($userdata->from < 2)
{
    // load last top players
    $filename = dirname(__FILE__) . '/cache/leaderboard_' . $league->id . '_last_3.txt';
    if (file_exists($filename))
    {
        $res->last = json_decode( file_get_contents($filename) );
    }

    $filename = dirname(__FILE__) . '/cache/leaderboard_' . $league->id . '_0_100.txt';
    if (file_exists($filename))
    {
        $res->current = json_decode( file_get_contents($filename) );
    }

    send("ok", $res);
    exit();
}

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league l LEFT JOIN profile p on l.profile_id=p.id WHERE l.league_id=$userdata->id && l.rank>=$userdata->from ORDER BY l.rank ASC LIMIT $userdata->count;");

$rows = array();
while($r = $db->result->fetch_assoc())
{
    $rows[] = $r;
}
$res->current = $rows;
$db->close();

send("ok", $res);

?>