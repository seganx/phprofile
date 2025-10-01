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

$db->query("SELECT private_data FROM profile_data WHERE profile_id='{$token->profile_id}' AND device_id='{$token->device_id}'");
if ($db->has_result())
{
    send('ok', $db->result->fetch_assoc()['private_data']);
}
else
{
    send('ok', null);
}
$db->close();

?>
