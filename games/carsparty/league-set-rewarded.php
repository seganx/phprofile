<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

// validate token
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

// validate current score value with database
if (queue_add("UPDATE league SET end_score=0, end_rank=0 WHERE profile_id=$token->profile_id AND league_id=$userdata->id"))
    send("ok", null);
else
    send_error(sxerror::server_maintenance);
?>