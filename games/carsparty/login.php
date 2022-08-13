<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';

$userjson= get_post_json();
$userjson->device_id = addslashes($userjson->device_id);

if ($userjson->game_id != configs::game_id || $userjson->device_id == 'none')
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

$tokenobj = new stdClass();
$tokenobj->game_id = $userjson->game_id;
$tokenobj->device_id = $userjson->device_id;

$db->query("SELECT id FROM profile WHERE device_id='$userjson->device_id'");
if ($db->no_result())
{
    $db->query("INSERT INTO profile (device_id) VALUES ('$userjson->device_id')");
    $tokenobj->profile_id = ''.$db->insert_id();
    $username = id_to_username($tokenobj->profile_id);
    $password = hash_base(rand(1000000000, 4000000000), 10, 32);
    $db->query("UPDATE profile SET username='$username', password='$password' WHERE id=$tokenobj->profile_id");
}
else
{
    $prof = $db->result->fetch_assoc();
    $tokenobj->profile_id = $prof['id'];
}
$db->close();

$token = base64_encode(json_encode($tokenobj));
$token .= '_' . crc32($token . configs::token_salt);

send('ok', $token);
?>
