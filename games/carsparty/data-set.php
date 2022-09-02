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
$userdata->private_data = addslashes($userdata->private_data);
$userdata->public_data = addslashes($userdata->public_data);

$strquery = '';
if (!empty($userdata->private_data) && !empty($userdata->public_data))
{
    $strquery = "UPDATE profile_data SET private_data='{$userdata->private_data}', public_data='{$userdata->public_data}' WHERE profile_id='{$token->profile_id}'";
}
else if (!empty($userdata->private_data))
{
    $strquery = "UPDATE profile_data SET private_data='{$userdata->private_data}' WHERE profile_id='{$token->profile_id}'";
}
else if (!empty($userdata->public_data))
{
    $strquery = "UPDATE profile_data SET public_data='{$userdata->public_data}' WHERE profile_id='{$token->profile_id}' AND device_id='{$token->device_id}'";
}

if (!empty($strquery) && queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
