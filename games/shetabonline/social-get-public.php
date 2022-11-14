<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

$userdata = get_post_json();
if (!isset($userdata->username))
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

$userdata->username = addslashes($userdata->username);
$owner_id = username_to_id($userdata->username);

// first fetch public data
$result = new stdclass();
$db->query("SELECT public_data FROM profile_data WHERE profile_id={$owner_id}");
if ($db->has_result())
{
    $result->data = $db->result->fetch_assoc()['public_data'];
}
else
{
    send_error(sxerror::invalid_params);
    $db->close();
    exit();
}

$db->query("SELECT assets.asset_id, assets.views, assets.likes, likes.liked FROM assets LEFT JOIN likes ON likes.profile_id={$token->profile_id} AND likes.owner_id={$owner_id} AND likes.asset_id=assets.asset_id WHERE assets.profile_id={$owner_id};");
if ($db->has_result())
{
    $result->assets = [];
    while($r = $db->result->fetch_assoc())
    {
        $result->assets[] = $r;
    }
}

send('ok', $result);

$db->close();

?>
