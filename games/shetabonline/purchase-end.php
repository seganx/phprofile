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
$userdata->version = addslashes($userdata->version);
$userdata->market = addslashes($userdata->market);
$userdata->sku = addslashes($userdata->sku);
$userdata->price = intval($userdata->price);
$userdata->token = addslashes($userdata->token);

if (queue_add("INSERT INTO `purchases` (`profile_id`, `version`, `market`, `sku`, `price`, `token`, `status`) VALUES ({$token->profile_id}, '{$userdata->version}', '{$userdata->market}', '{$userdata->sku}', {$userdata->price}, '{$userdata->token}', 1) ON DUPLICATE KEY UPDATE `status`=1, `price`={$userdata->price}"))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
