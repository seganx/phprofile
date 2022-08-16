<?php

require '_errors.php';
require '_configs.php';
require '_league.php';
require '_database.php';
require '_utilities.php';


$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();
}
$userdata = get_post_json();
$userdata->name = addslashes($userdata->name);

// validate league id
$league = league::get_all_leagues()[$userdata->name];
if ($league == null)
{
    send_error(sxerror::invalid_params);
    exit();
}

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$db->query("SELECT score, rank, end_score, end_rank FROM league_{$userdata->name} WHERE profile_id=$token->profile_id");
if ($db->has_result())
{
    send('ok', $db->result->fetch_assoc());
}
else
{
    $temp = new stdClass();
    $temp->score = $league->base_score;
    $temp->rank = rand(100000, 400000);
    $temp->end_score = 0;
    $temp->end_rank = 0;

    if (queue_add("INSERT INTO league_{$userdata->name} (profile_id, score, rank) VALUES ('$token->profile_id', '$temp->score', '$temp->rank')"))
    {
        send('ok', $temp);
    }
    else send_error(sxerror::server_maintenance);
}
$db->close();


?>