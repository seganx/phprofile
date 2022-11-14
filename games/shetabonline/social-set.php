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
        $id = "{$owner_id}_{$item->asset_id}";
        $strquery .= "INSERT INTO assets (id, profile_id, asset_id, views, likes) VALUES ('{$id}', {$owner_id}, {$item->asset_id}, {$item->view}, $insert_like) ON DUPLICATE KEY UPDATE views=views+{$item->view}, likes=GREATEST(0, likes+{$item->like});";
        
        $id = "{$token->profile_id}_{$owner_id}_{$item->asset_id}";
        $strquery .= "INSERT INTO likes (id, profile_id, owner_id, asset_id, liked) VALUES ('{$id}', {$token->profile_id}, {$owner_id}, {$item->asset_id}, $insert_like) ON DUPLICATE KEY UPDATE liked={$insert_like};";
    }
}
if (!empty($strquery) && queue_add($strquery))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
