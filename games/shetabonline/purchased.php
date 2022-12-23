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
$userdata->market = addslashes($userdata->market);
$userdata->item = addslashes($userdata->item);
$userdata->price = intval($userdata->price);
$userdata->token = addslashes($userdata->token);

if (queue_add("INSERT INTO `purchases` (`profile_id`, `market`, `sku`, `price`, `token`) VALUES ({$token->profile_id}, '{$userdata->market}', '{$userdata->sku}', {$userdata->price}, '{$userdata->token}')"))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
