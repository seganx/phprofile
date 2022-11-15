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

$db->query("SELECT data FROM assets WHERE profile_id='{$token->profile_id}'");
if ($db->has_result())
{
    $result = new stdclass();
    $result->assets = [];
    $assets = json_decode($db->result->fetch_assoc()['data'], true);
    foreach ($assets as $key => $value)
    {
        $item = new stdclass();
        $item->asset = $key;
        $item->views = $value[0];
        $item->likes = $value[1];
        $result->assets[] = $item;
    }
    send('ok', $result);
}
else
{
    send('ok', null);
}
$db->close();

?>
