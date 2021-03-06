<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

$userdata = get_post_json();
$userdata->hash = addslashes($userdata->hash);
$userdata->private_data = addslashes($userdata->private_data);
$userdata->public_data = addslashes($userdata->public_data);

$strquery = "";
if (empty($userdata->hash) == false)
{
    $strquery = "UPDATE profile SET datahash='$userdata->hash' WHERE id='$token->profile_id';";
}

if (empty($userdata->private_data) == false && empty($userdata->public_data) == false)
{
    $strquery .= "INSERT INTO profile_data (profile_id, private_data, public_data) VALUES ('$token->profile_id','$userdata->private_data','$userdata->public_data') ".
    "ON DUPLICATE KEY UPDATE private_data='$userdata->private_data', public_data='$userdata->public_data';";
}
else if (empty($userdata->private_data) == false)
{
    $strquery .= "INSERT INTO profile_data (profile_id, private_data) VALUES ('$token->profile_id','$userdata->private_data') ".
    "ON DUPLICATE KEY UPDATE private_data='$userdata->private_data';";
}
else if (empty($userdata->public_data) == false)
{
    $strquery.= "INSERT INTO profile_data (profile_id, public_data) VALUES ('$token->profile_id','$userdata->public_data') ".
    "ON DUPLICATE KEY UPDATE public_data='$userdata->public_data';";
}

if (queue_add($strquery))
    send("ok", null);
else
    send_error(sxerror::server_maintenance);
?>
