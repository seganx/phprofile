<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

$userdata = get_post_json();

$private_data = isset($userdata->private_data) ? (string)$userdata->private_data : '';
$public_data = isset($userdata->public_data) ? (string)$userdata->public_data : '';
$set_private = empty($private_data) ? 0 : 1;
$set_public = empty($public_data) ? 0 : 1;
$gems = isset($userdata->gems) ? intval($userdata->gems) : 0;
$golds = isset($userdata->golds) ? intval($userdata->golds) : 0;

$strquery = "CALL `profile_data_set`(";
$strquery .= intval($token->profile_id) . ", ";
$strquery .= sql_quote($token->device_id) . ", ";
$strquery .= $gems . ", ";
$strquery .= $golds . ", ";
$strquery .= sql_quote($private_data) . ", ";
$strquery .= sql_quote($public_data) . ", ";
$strquery .= $set_private . ", ";
$strquery .= $set_public . ")";

if (($set_private || $set_public) && queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
