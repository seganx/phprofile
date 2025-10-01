<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';

$userjson= get_post_json();
$userjson->device_id = addslashes($userjson->device_id);
$userjson->client_id = addslashes($userjson->client_id);

if ($userjson->game_id != configs::game_id || strlen($userjson->device_id) != 32 || empty($userjson->device_id))
{
    send_error(sxerror::invalid_params);
    exit();
}

$client_version_array = explode('.', $userjson->client_id);
if (count($client_version_array) < 2)
{
	send(sxerror::invalid_params, 'invalid');
    exit();
}

$client_version = intval($client_version_array[0]);
if ($client_version <= 70)
{
	send(sxerror::invalid_params, 'update');
    exit();
}

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$password = '';
$client_id = '';

$tokenobj = new stdClass();
$tokenobj->game_id = $userjson->game_id;
$tokenobj->device_id = $userjson->device_id;

$db->query("SELECT `id`, `client_id`, `password` FROM `profile` WHERE `device_id`='{$userjson->device_id}'");
if ($db->no_result())
{
    $db->query("INSERT INTO `profile` (`device_id`, `join_build`, `client_id`) VALUES ('{$userjson->device_id}', '{$userjson->client_id}', '{$userjson->client_id}')");
    $tokenobj->profile_id = ''.$db->insert_id();
    $username = id_to_username($tokenobj->profile_id);
    $password = hash_base(rand(1000000000, 4000000000), 10, 32);
    $db->query("UPDATE `profile` SET `username`='{$username}', `password`='{$password}' WHERE `id`={$tokenobj->profile_id}");

    queue_add("INSERT INTO `profile_data` (`profile_id`, `device_id`) VALUES ('{$tokenobj->profile_id}', '{$tokenobj->device_id}') ON DUPLICATE KEY UPDATE `profile_id`=`profile_id`");
}
else
{
    $row = $db->result->fetch_assoc();
    $tokenobj->profile_id = $row['id'];
    $password = $row['password'];
	$client_id = $row['client_id'];
}
$db->close();

if ($password == 'fraud')
{
    send(sxerror::invalid_params, 'fraud');
}
else if (strcmp($userjson->client_id, $client_id) < 0)
{
	send(sxerror::invalid_params, 'update');
}
else
{
    $token = base64_encode(json_encode($tokenobj));
    $token .= '_' . crc32($token . configs::token_salt);
    send('ok', $token);

	if ($client_id != $userjson->client_id)
	{
		queue_add("UPDATE `profile` SET `client_id`='{$userjson->client_id}' WHERE `id`={$tokenobj->profile_id}");
	}
}
?>
