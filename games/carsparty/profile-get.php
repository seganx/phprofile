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

$db->query("SELECT username, password, nickname, status, avatar FROM profile WHERE id=$token->profile_id");
if ($db->has_result())
{
    send("ok", $db->result->fetch_assoc());
}
else
{
    send_error(sxerror::invalid_token);
}
$db->close();

?>
