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
if (!isset($userdata->name) || !isset($userdata->score) || !isset($userdata->value) || !isset($userdata->hash))
{
    send_error(sxerror::invalid_params);
    exit();
}

$userdata->score = intval($userdata->score);
$userdata->value = intval($userdata->value);

$hash = md5('seganx_' . $userdata->score . '!&!' . $userdata->value . '#(' . $userdata->name . configs::hash_salt);
if ($userdata->hash !== $hash)
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$leagues = league::get_all_leagues();
$league = isset($leagues[$userdata->name]) ? $leagues[$userdata->name] : null;
if ($league == null || $userdata->value <= 0 || $league->max_value < $userdata->value)
{
    send_error(sxerror::invalid_params);
    exit();
}
$finalscore = intval($userdata->score + $userdata->value);

$strquery = "CALL `league_{$league->name}_add_score`(";
$strquery .= intval($token->profile_id) . ", ";
$strquery .= sql_quote($token->device_id) . ", ";
$strquery .= intval($userdata->score) . ", ";
$strquery .= intval($userdata->value) . ", ";
$strquery .= intval($league->base_score) . ")";

if (queue_add($strquery))
{
    send('ok', $finalscore);
}
else
{
    send_error(sxerror::server_maintenance);
}

?>
