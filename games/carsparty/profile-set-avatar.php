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
$userdata->avatar = addslashes($userdata->avatar);
if (queue_add("UPDATE profile SET avatar='$userdata->avatar' WHERE id='$token->profile_id';"))
    send("ok", null);
else
    send_error(sxerror::invalid_params);
?>