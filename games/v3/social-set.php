<?php

require '_errors.php';
require '_configs.php';
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

$owner_id = username_to_id($userdata->username);

if($token->profile_id == $owner_id)
{
    send_error(sxerror::invalid_params);
    exit();
}

if (!isset($userdata->changes) || !is_array($userdata->changes))
{
    send_error(sxerror::invalid_params);
    exit();
}

$query_array = array();
foreach ($userdata->changes as $item)
{
    if (isset($item->asset_id) && isset($item->view) && isset($item->like) && is_int($item->asset_id) && is_int($item->view) && is_int($item->like))
    {
        $item->asset_id = max(0, $item->asset_id);
        $item->view = max(0, min(1, $item->view));
        $item->like = max(-1, min(1, $item->like));
        $asset_name = '$.a' . $item->asset_id;
        $query_array[] = "CALL `social_update_reaction`(" . intval($token->profile_id) . ", " . intval($owner_id) . ", " . sql_quote($asset_name) . ", " . intval($item->view) . ", " . intval($item->like) . ")";
    }
}
$strquery = join(";", $query_array);

if (!empty($strquery) && queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
