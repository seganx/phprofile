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
$userjson->username = addslashes($userjson->username);
$userjson->password = addslashes($userjson->password);

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

// search for target profile
$db->query("SELECT id FROM profile WHERE username='{$userjson->username}' and password='{$userjson->password}'");
if ($db->no_result())
{
    send_error(sxerror::invalid_userpass);
    exit();
}
$targetProfileId = $db->result->fetch_assoc()['id'];

$leagues = league::get_all_leagues();

// detach current profile
$strquery = "UPDATE profile SET device_id='none' WHERE id={$usertoken->profile_id};";
$strquery .= "UPDATE profile_data SET device_id='none' WHERE profile_id={$usertoken->profile_id};";
//$strquery .= "UPDATE friends SET device_id='none' WHERE profile_id={$usertoken->profile_id};";
foreach ($leagues as $key => $item)
{
    $strquery .= "UPDATE league_{$item->name} SET device_id='none' WHERE profile_id={$usertoken->profile_id};";
}

// attach to the profile and other tables
$strquery .= "UPDATE profile SET device_id='{$usertoken->device_id}' WHERE id={$targetProfileId};";
$strquery .= "UPDATE profile_data SET device_id='{$usertoken->device_id}' WHERE profile_id={$targetProfileId};";
//$strquery .= "UPDATE friends SET device_id='{$usertoken->device_id}' WHERE profile_id={$targetProfileId};";
foreach ($leagues as $key => $item)
{
    $strquery .= "UPDATE league_{$item->name} SET device_id='{$usertoken->device_id}' WHERE profile_id={$targetProfileId};";
}

$db->multi_query($strquery);
$db->close();

$usertoken->profile_id = $targetProfileId;

$token = base64_encode(json_encode($usertoken));
$token .= '_' . crc32($token . configs::token_salt);

send('ok', $token);
?>
