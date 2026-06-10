<?php

require '_errors.php';
require '_configs.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

$userdata = get_post_json();
if (!isset($userdata->avatar))
{
    send_error(sxerror::invalid_params);
    exit();
}

if (queue_add("UPDATE `profile` SET `avatar`=" . sql_quote($userdata->avatar) . " WHERE `id`=" . intval($token->profile_id) . " AND `device_id`=" . sql_quote($token->device_id)))
    send('ok', null);
else
    send_error(sxerror::invalid_params);
?>
