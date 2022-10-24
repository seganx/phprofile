<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

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

$db->query("SELECT f.id, p.username, p.nickname, p.status, p.avatar, s.level, f.data FROM friends f LEFT JOIN profile p ON p.id=f.friend_id LEFT JOIN stats s ON s.profile_id=f.friend_id WHERE f.profile_id=$token->profile_id");

$rows = array();
while($r = $db->result->fetch_assoc())
{
    $rows[] = $r;
}
$db->close();

send('ok', $rows);

?>