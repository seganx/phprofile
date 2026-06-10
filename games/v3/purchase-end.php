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
if (!isset($userdata->version) || !isset($userdata->market) || !isset($userdata->sku) || !isset($userdata->price) || !isset($userdata->token))
{
    send_error(sxerror::invalid_params);
    exit();
}

$query = "CALL `purchase_record`(" . intval($token->profile_id)
    . ", " . sql_quote($userdata->version)
    . ", " . sql_quote($userdata->market)
    . ", " . sql_quote($userdata->sku)
    . ", " . intval($userdata->price)
    . ", " . sql_quote($userdata->token)
    . ", 1)";

if (queue_add($query))
    send('ok', null);
else
    send_error(sxerror::server_maintenance);
?>
