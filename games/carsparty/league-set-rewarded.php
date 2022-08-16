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
$userdata->name = addslashes($userdata->name);

// validate league id
$league = league::get_all_leagues()[$userdata->name];
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate current score value with database
if (queue_add("UPDATE league_{$userdata->name} SET end_score=0, end_rank=0 WHERE profile_id={$token->profile_id}"))
    send("ok", null);
else
    send_error(sxerror::server_maintenance);
?>