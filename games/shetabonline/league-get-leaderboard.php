<?php

require '_errors.php';
require '_configs.php';
require '_league.php';
require '_database.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}
$userdata = get_post_json();
$userdata->name = addslashes($userdata->name);

// validate league id
$league = league::get_all_leagues()[$userdata->name];
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

$userdata->from = intval($userdata->from);
$userdata->count = clamp(intval($userdata->count), 5, 100);
$res = new stdClass();

// check to see if top players requested
if ($userdata->from < 2)
{
    // load last top players
    $filename = dirname(__FILE__) . '/cache/leaderboard_' . $league->name . '_last_3.txt';
    if (file_exists($filename))
    {
        $res->last = json_decode( file_get_contents($filename) );
    }

    $filename = dirname(__FILE__) . '/cache/leaderboard_' . $league->name . '_0_100.txt';
    if (file_exists($filename))
    {
        $res->current = json_decode( file_get_contents($filename) );
    }

    $filename = dirname(__FILE__) . '/cache/leaderboard_' . $league->name . '_total_0_100.txt';
    if (file_exists($filename))
    {
        $res->total = json_decode( file_get_contents($filename) );
    }

    send('ok', $res);
    exit();
}

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT p.username, p.nickname, p.status, p.avatar, l.score, l.rank FROM league l LEFT JOIN profile p on l.profile_id=p.id WHERE l.rank>={$userdata->from} ORDER BY l.rank ASC LIMIT {$userdata->count}");

$rows = array();
while($r = $db->result->fetch_assoc())
{
    $rows[] = $r;
}
$res->current = $rows;
$db->close();

send('ok', $res);

?>