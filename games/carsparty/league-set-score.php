<?php

require '_errors.php';
require '_configs.php';
require '_league.php';
require '_utilities.php';

// validate token
$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

// validate user data
$userdata = get_post_json();
$userdata->id = intval($userdata->id);
$userdata->score = intval($userdata->score);
$userdata->value = intval($userdata->value);
$hash = md5('seganx_' . $userdata->score . '!&!' . $userdata->value . '#(' . $userdata->id);
if ($userdata->hash !== $hash)
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$league = league::get_all_leagues()[$userdata->id];
if ($league == null || $league->max_value < $userdata->value)
{
    send_error(sxerror::invalid_params);
    exit();
}

$finalscore = intval($league->base_score + $userdata->value);
if (queue_add("UPDATE league SET score=$finalscore WHERE profile_id=$token->profile_id AND league_id=$userdata->id;"))
{
    send('ok', $finalscore);
}
else
{
    send_error(sxerror::server_maintenance);
}

?>