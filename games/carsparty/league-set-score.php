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
$userdata->name = addslashes($userdata->name);
$userdata->score = intval($userdata->score);
$userdata->value = intval($userdata->value);

$hash = md5('seganx_' . $userdata->score . '!&!' . $userdata->value . '#(' . $userdata->name . configs::hash_salt);
if ($userdata->hash !== $hash)
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$league = league::get_all_leagues()[$userdata->name];
if ($league == null || $league->max_value < $userdata->value)
{
    send_error(sxerror::invalid_params);
    exit();
}

$finalscore = intval($userdata->score + $userdata->value);
if (queue_add("UPDATE league_{$userdata->name} SET score='{$finalscore}' WHERE profile_id='{$token->profile_id}' AND device_id='{$token->device_id}'"))
{
    send('ok', $finalscore);
}
else
{
    send_error(sxerror::server_maintenance);
}

?>