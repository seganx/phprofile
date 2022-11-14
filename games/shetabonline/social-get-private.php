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

$db->query("SELECT asset_id, views, likes FROM assets WHERE profile_id='{$token->profile_id}'");
if ($db->has_result())
{
    $rows = array();
    while($r = $db->result->fetch_assoc())
    {
        $rows[] = $r;
    }
    send('ok', $rows);
}
else
{
    send('ok', null);
}
$db->close();

?>
