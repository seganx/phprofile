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
$userdata->sku = addslashes($userdata->sku);
$userdata->token = addslashes($userdata->token);
$userdata->payload = addslashes($userdata->payload);

if (queue_add("INSERT INTO purchases (version, token, profile_id, sku, payload) VALUES ($userdata->version, '$userdata->token', '$token->profile_id', '$userdata->sku', '$userdata->payload') ON DUPLICATE KEY UPDATE profile_id='$token->profile_id', version=$userdata->version, sku='$userdata->sku', payload='$userdata->payload';"))
    send("ok", null);
else
    send_error(sxerror::invalid_params);

?>