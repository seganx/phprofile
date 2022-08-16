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
$userdata->id = intval($userdata->id);

// validate league id
$league = league::get_all_leagues()[$userdata->id];
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT score, rank, end_score, end_rank FROM league WHERE profile_id=$token->profile_id AND league_id=$userdata->id");
if ($db->has_result())
{
    send("ok", $db->result->fetch_assoc());
}
else
{
    send_error(sxerror::league_empty);
}
$db->close();


?>