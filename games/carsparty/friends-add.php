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

// verify that profile id exist for this username
$db->query("SELECT id FROM profile WHERE username='$userdata->username'");
if ($db->no_result())
{
    send_error(sxerror::invalid_params);
    $db->close();
    exit();
}

// avoid self friend :)
$friend_id = $db->result->fetch_assoc()['id'];
if ($token->profile_id == $friend_id)
{
    send_error(sxerror::invalid_params);
    $db->close();
    exit();
}

// check to see if record exist
$rowid = 0;
$db->query("SELECT id FROM friends WHERE profile_id=$token->profile_id && friend_id=$friend_id");
if ($db->no_result())
{
    queue_add("INSERT INTO friends (profile_id, friend_id) VALUES ($friend_id, $token->profile_id)");
    $db->query("INSERT INTO friends (profile_id, friend_id) VALUES ($token->profile_id, $friend_id)");
    $rowid = $db->insert_id();
}
else $rowid = $db->result->fetch_assoc()['id'];

$db->query("SELECT f.id, p.username, p.nickname, p.status, p.avatar, s.level, f.data FROM friends f LEFT JOIN profile p ON p.id=f.friend_id LEFT JOIN stats s ON s.profile_id=f.friend_id WHERE f.id=$rowid");
$row = $db->result->fetch_assoc();
$db->close();

send("ok", $row);

?>
