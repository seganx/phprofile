<?php
require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';
require 'google/verify.php';

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

$response = verifyGooglePlayPurchase($userdata->sku, $userdata->token);
if (!$response) 
{
    send_error(sxerror::invalid_purchase);
    exit();
}
else if ($response->purchaseState != 0 || $response->consumptionState != 0)
{
    send_error(sxerror::invalid_purchase);
    exit();
}

$db = database::connect();
if ($db == null)
{
	sleep(10);
	$db = database::connect();
}
if ($db == null)
{
	sleep(10);
	$db = database::connect();
}
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT * FROM `purchases` WHERE `token`='{$userdata->token}'");
if ($db->has_result())
{
    $row = $db->result->fetch_assoc();
    if ($row['status'] == '1' || $row['profile_id'] != $token->profile_id || $row['sku'] != $userdata->sku)
        send_error(sxerror::invalid_purchase);
    else
        send('ok', null);
}
else
{
    if (queue_add("INSERT INTO `purchases` (`profile_id`, `version`, `market`, `sku`, `price`, `token`) VALUES ({$token->profile_id}, '{$userdata->version}', '{$userdata->market}', '{$userdata->sku}', {$userdata->price}, '{$userdata->token}') ON DUPLICATE KEY UPDATE `market`='{$userdata->market}'"))
        send('ok', null);
    else
        send_error(sxerror::server_maintenance);
}
$db->close();
?>
