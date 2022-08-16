<?php

require '_errors.php';
require '_configs.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

$userdata = get_post_json();
$userdata->status = addslashes($userdata->status);
if (queue_add("UPDATE profile SET status='{$userdata->status}' WHERE id='{$token->profile_id}'"))
    send('ok', null);
else
    send_error(sxerror::invalid_params);

?>
