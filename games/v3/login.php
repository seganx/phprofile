<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';

$userjson= get_post_json();

if (!isset($userjson->game_id) || !isset($userjson->device_id) || !isset($userjson->client_id)
    || $userjson->game_id != configs::game_id || strlen($userjson->device_id) != 32 || empty($userjson->device_id))
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

$device_id = $db->escape((string)$userjson->device_id);
$client_id_input = $db->escape((string)$userjson->client_id);

$password = '';
$client_id = '';

$tokenobj = new stdClass();
$tokenobj->game_id = $userjson->game_id;
$tokenobj->device_id = $userjson->device_id;

$db->query("SELECT `id`, `client_id`, `password` FROM `profile` WHERE `device_id`='{$device_id}'");
if ($db->no_result())
{
    $db->query("INSERT INTO `profile` (`device_id`, `join_date`, `join_build`, `client_id`) VALUES ('{$device_id}', CURDATE(), '{$client_id_input}', '{$client_id_input}')");
    $tokenobj->profile_id = ''.$db->insert_id();
    $username = id_to_username($tokenobj->profile_id);
    $password = hash_base(random_int(1000000000, 4000000000), 10, 32);
    $db->query("UPDATE `profile` SET `username`='{$username}', `password`='{$password}' WHERE `id`={$tokenobj->profile_id}");
    $client_id = (string)$userjson->client_id;

    queue_add("CALL `profile_data_create`(" . intval($tokenobj->profile_id) . ", " . sql_quote($tokenobj->device_id) . ")");
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
else if (!empty($client_id) && version_compare($userjson->client_id, $client_id, '<'))
{
	send(sxerror::invalid_params, 'update');
}
else
{
    $token = create_token($tokenobj);
    send('ok', $token);

	if ($client_id != $userjson->client_id)
	{
		queue_add("UPDATE `profile` SET `client_id`=" . sql_quote($userjson->client_id) . " WHERE `id`=" . intval($tokenobj->profile_id));
	}
}
?>
