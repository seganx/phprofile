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

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$profile_id = intval($token->profile_id);
$device_id = $db->escape((string)$token->device_id);
$db->query("SELECT `username`, `password`, `nickname`, `status`, `avatar` FROM `profile` WHERE `id`={$profile_id} AND `device_id`='{$device_id}'");
if ($db->has_result())
{
    send('ok', $db->result->fetch_assoc());
}
else
{
    send_error(sxerror::account_transfered);
}
$db->close();

?>
