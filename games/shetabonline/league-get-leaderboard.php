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

$basefilename = dirname(__FILE__) . '/cache/leaderboard_' . $league->name;
$res = new stdClass();

$filename = $basefilename . '_last_3.txt';
if (file_exists($filename))
{
    $res->last = json_decode( file_get_contents($filename) );
}

$filename = $basefilename . '_0_999999.txt'; // top100
if (file_exists($filename))
{
    $res->top100 = json_decode( file_get_contents($filename) );
}

$filename = $basefilename . '_overall.txt';
if (file_exists($filename))
{
    $res->total = json_decode( file_get_contents($filename) );
}

$userdata->min_score = intval($userdata->min_score);
$userdata->max_score = intval($userdata->max_score);

$filename = "{$basefilename}_{$userdata->min_score}_{$userdata->max_score}.txt";
if (file_exists($filename))
{
    $res->current = json_decode( file_get_contents($filename) );
}
else
{
	$res->current = $res->top100;	
}


send('ok', $res);

?>