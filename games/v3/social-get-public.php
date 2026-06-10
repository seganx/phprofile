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

$owner_id = username_to_id($userdata->username);
$viewer_id = intval($token->profile_id);

$data = array();
$db->query("CALL social_get_public({$owner_id}, {$viewer_id})");
if ($db->has_result())
{
    $data = $db->result->fetch_assoc();
}
else
{
    send_error(sxerror::invalid_params);
    $db->close();
    exit();
}
$db->close();

$result = new stdclass();
$result->data = isset($data['public_data']) ? $data['public_data'] : null;

if (isset($data['assets']))
{
    $result->assets = array();
    $assets = json_decode($data['assets'], true);
    if (is_array($assets))
    {
        $keys = array_keys($assets);
        $keys_count = count($keys);
        for ($i = 0; $i < $keys_count; $i++)
        {
            $key = $keys[$i];
            $value = $assets[$key];
            if (!is_array($value)) continue;

            $item = new stdclass();
            $item->asset = $key;
            $item->views = intval(isset($value[0]) ? $value[0] : 0);
            $item->likes = intval(isset($value[1]) ? $value[1] : 0);
            $result->assets[] = $item;
        }
    }
}

if (isset($data['likes']))
{
    $result->likes = array();
    $reactions = json_decode($data['likes'], true);
    if (is_array($reactions))
    {
        $keys = array_keys($reactions);
        $keys_count = count($keys);
        for ($i = 0; $i < $keys_count; $i++)
        {
            $key = $keys[$i];
            $value = $reactions[$key];

            $item = new stdclass();
            $item->asset = $key;
            $item->liked = is_array($value) ? intval(isset($value[1]) ? $value[1] : 0) : intval($value);
            $result->likes[] = $item;
        }
    }
}

send('ok', $result);

?>
