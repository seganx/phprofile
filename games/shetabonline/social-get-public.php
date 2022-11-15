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

$data = [];
$db->query("CALL social_get_public({$owner_id}, {$token->profile_id})");
if ($db->has_result())
{
    $data = $db->result->fetch_assoc();
}
else
{
    send_error(sxerror::invalid_params);
}
$db->close();

$result = new stdclass();
$result->data = $data['public_data'];

if (isset($data['assets']))
{
    $result->assets = [];
    $assets = json_decode($data['assets'], true);
    foreach ($assets as $key => $value)
    {
        $item = new stdclass();
        $item->asset = $key;
        $item->views = $value[0];
        $item->likes = $value[1];
        $result->assets[] = $item;
    }
}

if (isset($data['likes']))
{
    $result->likes = [];
    $assets = json_decode($data['likes'], true);
    foreach ($assets as $key => $value)
    {
        $item = new stdclass();
        $item->asset = $key;
        $item->liked = $value;
        $result->likes[] = $item;
    }
}

send('ok', $result);

?>
