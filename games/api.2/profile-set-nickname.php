<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

$userdata = get_post_json();
$userdata->nickname = addslashes($userdata->nickname);
if (queue_add("UPDATE profile SET nickname='$userdata->nickname' WHERE id='$token->profile_id';"))
    send("ok", null);
else
    send_error(sxerror::invalid_params);
?>
