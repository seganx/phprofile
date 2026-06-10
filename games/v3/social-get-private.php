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

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$profile_id = intval($token->profile_id);
$db->query("SELECT `data` FROM `social_asset_stats` WHERE `owner_profile_id`={$profile_id}");
if ($db->has_result())
{
    $result = new stdclass();
    $result->assets = array();
    $assets = json_decode($db->result->fetch_assoc()['data'], true);
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
    send('ok', $result);
}
else
{
    send('ok', null);
}
$db->close();

?>
