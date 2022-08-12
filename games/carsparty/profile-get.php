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

$prof = null;
$db->query("SELECT username, password, nickname, status, avatar, datahash FROM profile WHERE id=$token->profile_id");
if ($db->has_result())
{
    $prof = $db->result->fetch_assoc();
}
$db->close();

if (empty($prof))
{
    send_error(sxerror::invalid_token);
}
else
{
    send("ok", $prof);    
}

?>
