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
$userdata = get_post_json();
if (!isset($userdata->name))
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$leagues = league::get_all_leagues();
$league = isset($leagues[$userdata->name]) ? $leagues[$userdata->name] : null;
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate current score value with database
$strquery = "UPDATE `league_{$league->name}` SET `end_score`=0, `end_rank`=0 WHERE `profile_id`=";
$strquery .= intval($token->profile_id) . " AND `device_id`=" . sql_quote($token->device_id);

if (queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
