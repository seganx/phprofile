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

$res = null;
$db->query("SELECT private_data, public_data FROM profile_data WHERE profile_id=$token->profile_id");
if ($db->has_result())
{
    $res = $db->result->fetch_assoc();
}
$db->close();

if (empty($res))
{
    $tmp = new stdClass();
    $tmp->private_data = null;
    $tmp->public_data = null;
    send("ok", $tmp);
}
else 
{
    send("ok", $res);
}

?>
