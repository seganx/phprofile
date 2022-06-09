<?php

require '_classes.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

$userdata = get_post_json();
$userdata->version = intval($userdata->version);
$userdata->gems = intval($userdata->gems);
$userdata->skill = intval($userdata->skill);
$userdata->level = intval($userdata->level);

$query = "INSERT INTO stats (game_id,version,profile_id,gems,skill,level) VALUES ('$token->game_id', $userdata->version, '$token->profile_id', '$userdata->gems', '$userdata->skill', '$userdata->level') ".
"ON DUPLICATE KEY UPDATE game_id='$token->game_id', version=$userdata->version, gems='$userdata->gems', skill='$userdata->skill', level='$userdata->level';";

if (queue_add($query))
    send("ok", null);
else
    send_error(sxerror::invalid_params);
?>