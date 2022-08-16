<?php

require '_classes.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}

$userdata = get_post_json();
$userdata->username = addslashes($userdata->username);
$userdata->data = addslashes($userdata->data);

if (empty($userdata->username))
{
    send_error(sxerror::invalid_params);
    exit();
}

$friend_id = username_to_id($userdata->username);

if (queue_add("UPDATE friends SET data='$userdata->data' WHERE (profile_id=$friend_id AND friend_id=$token->profile_id)"))
    send("ok", null);
else
    send_error(sxerror::invalid_params);

?>
