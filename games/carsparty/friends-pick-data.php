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
$userdata->id = intval($userdata->id);

$db->query("SELECT data FROM friends WHERE id=$userdata->id");
$res = $db->result->fetch_assoc()['data'];
$db->close();

send('ok', $res);

?>
