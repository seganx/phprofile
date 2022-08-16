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
if (!isset($userdata->username))
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

$userdata->username = addslashes($userdata->username);
$profile_id = username_to_id($userdata->username);

$db->query("SELECT public_data FROM profile_data WHERE profile_id=$profile_id");
if ($db->has_result())
{
    send("ok", $db->result->fetch_assoc()['public_data']);
}
else
{
    send_error(sxerror::invalid_params);
}
$db->close();

?>
