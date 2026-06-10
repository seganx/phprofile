<?php

require '_errors.php';
require '_configs.php';
require '_league.php';
require '_utilities.php';
require '_database.php';

$usertoken = get_token();
if ($usertoken == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

if ($usertoken->game_id != configs::game_id)
{
    send_error(sxerror::invalid_params);
    exit();
}

$userjson= get_post_json();
if (!isset($userjson->username) || !isset($userjson->password))
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

$username = $db->escape((string)$userjson->username);
$password = $db->escape((string)$userjson->password);
$device_id = $db->escape((string)$usertoken->device_id);
$current_profile_id = intval($usertoken->profile_id);

// search for target profile
$db->query("SELECT `id` FROM `profile` WHERE `username`='{$username}' and `password`='{$password}'");
if ($db->no_result())
{
    send_error(sxerror::invalid_userpass);
    $db->close();
    exit();
}
$targetProfileId = intval($db->result->fetch_assoc()['id']);

$leagues = league::get_all_leagues();

function transfer_query($db, string $query): bool
{
    if ($db->query($query) === false)
    {
        $db->query("ROLLBACK");
        return false;
    }
    return true;
}

$ok = true;
$db->query("START TRANSACTION");

$ok = $ok && transfer_query($db, "UPDATE `profile` SET `device_id`='none' WHERE `id`={$current_profile_id}");
$ok = $ok && transfer_query($db, "UPDATE `profile_data` SET `device_id`='none' WHERE `profile_id`={$current_profile_id}");
foreach ($leagues as $item)
{
    $ok = $ok && transfer_query($db, "UPDATE `league_{$item->name}` SET `device_id`='none' WHERE `profile_id`={$current_profile_id}");
}

$ok = $ok && transfer_query($db, "UPDATE `profile` SET `device_id`='{$device_id}' WHERE `id`={$targetProfileId}");
$ok = $ok && transfer_query($db, "UPDATE `profile_data` SET `device_id`='{$device_id}' WHERE `profile_id`={$targetProfileId}");
foreach ($leagues as $item)
{
    $ok = $ok && transfer_query($db, "UPDATE `league_{$item->name}` SET `device_id`='{$device_id}' WHERE `profile_id`={$targetProfileId}");
}

if ($ok)
    $db->query("COMMIT");

$db->close();

if (!$ok)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$usertoken->profile_id = $targetProfileId;

$token = create_token($usertoken);

send('ok', $token);
?>
