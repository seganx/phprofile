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
if (!isset($userdata->version) || !isset($userdata->market) || !isset($userdata->sku) || !isset($userdata->price) || !isset($userdata->token))
{
    send_error(sxerror::invalid_params);
    exit();
}

$userdata->price = intval($userdata->price);

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

$purchase_token = $db->escape((string)$userdata->token);
$db->query("SELECT * FROM `purchases` WHERE `token`='{$purchase_token}'");
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
    $query = "CALL `purchase_record`(" . intval($token->profile_id)
        . ", " . sql_quote($userdata->version)
        . ", " . sql_quote($userdata->market)
        . ", " . sql_quote($userdata->sku)
        . ", " . intval($userdata->price)
        . ", " . sql_quote($userdata->token)
        . ", 0)";

    if (queue_add($query))
        send('ok', null);
    else
        send_error(sxerror::server_maintenance);
}
$db->close();
?>
