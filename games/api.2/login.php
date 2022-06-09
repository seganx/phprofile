<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

$userjson= get_post_json();
$userjson->game_id = intval($userjson->game_id);
$userjson->device_id = addslashes($userjson->device_id);

if (is_game_valid($userjson->game_id) == false)
{
    send_error(sxerror::invalid_params);
    exit();
}

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT * FROM profile WHERE game_id=$userjson->game_id AND device_id='$userjson->device_id'");
if ($db->no_result())
{
    $db->query("INSERT INTO profile (game_id, device_id) VALUES ($userjson->game_id, '$userjson->device_id')");
    $newid = $db->insert_id();
    $username = id_to_username($newid);
    $password = hash_base(rand(1000000000, 4000000000), 10, 32);
    $db->query("UPDATE profile SET username='$username', password='$password' WHERE id=$newid");
    $db->query("SELECT * FROM profile WHERE id=$newid");
}
$prof = $db->result->fetch_assoc();
$db->close();

$tokenobj = new stdClass();
$tokenobj->game_id = $userjson->game_id;
$tokenobj->profile_id = $prof['id'];
$tokenobj->device_id = $prof['device_id'];

$token = base64_encode(json_encode($tokenobj));
$token .= "_" . crc32($token . "seganx_games");

send("ok", $token);
?>
