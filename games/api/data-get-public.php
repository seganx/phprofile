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

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$userdata = get_post_json();
$userdata->username = addslashes($userdata->username);
$profile_id = username_to_id($userdata->username);
$res = null;

$db->query("SELECT public_data FROM profile_data WHERE profile_id=$profile_id");
if ($db->has_result())
{
    $res = $db->result->fetch_assoc()['public_data'];
}
$db->close();

if (empty($res))
{
    send_error(sxerror::invalid_params);
}
else 
{
    send("ok", $res);
}

?>
