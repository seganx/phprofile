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

$userdata->username = addslashes($userdata->username);
$owner_id = username_to_id($userdata->username);

if($token->profile_id == $owner_id)
{
    send_error(sxerror::invalid_params);
    exit();
}


$strquery = '';
foreach ($userdata->changes as $item)
{
    if (is_int($item->asset_id) && is_int($item->view) && is_int($item->like))
    {
        $item->asset_id = max(0, $item->asset_id);
        $item->view = max(0, min(1, $item->view));
        $item->like = max(-1, min(1, $item->like));
        $insert_like = max(0, $item->like);
        $asset_name = '$.a' . $item->asset_id;
        $strquery .= "CALL social_set({$token->profile_id}, {$owner_id}, '{$asset_name}', {$item->view}, {$item->like}, $insert_like);";
    }
}

if (!empty($strquery) && queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
