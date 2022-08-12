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

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$res = new stdClass();

$db->query("SELECT * FROM league WHERE profile_id=$token->profile_id AND league_id=$userdata->id");
if ($db->has_result())
{
    $prof = $db->result->fetch_assoc();
    $res->score = intval($prof['score']);
    $res->rank = intval($prof['rank']);
    $res->end_score = intval($prof['end_score']);
    $res->end_rank = intval($prof['end_rank']);
}
$db->close();

send("ok", $res);

?>