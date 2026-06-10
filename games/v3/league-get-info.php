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
if (!isset($userdata->name))
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$league = league::get_all_leagues();
$league = isset($league[$userdata->name]) ? $league[$userdata->name] : null;
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

$result = new stdClass();
$profile_id = intval($token->profile_id);
$device_id = $db->escape((string)$token->device_id);
$db->query("SELECT `score`, `rank`, `end_score`, `end_rank` FROM `league_{$league->name}` WHERE `profile_id`={$profile_id} AND `device_id`='{$device_id}'");
if ($db->has_result())
{
    $row = $db->result->fetch_assoc();
    $result->start_time = $league->start_time;
    $result->duration = $league->duration;
    $result->score = intval($row['score']);
    $result->rank = intval($row['rank']);
    $result->end_score = intval($row['end_score']);
    $result->end_rank = intval($row['end_rank']);
    send('ok', $result);
}
else
{
    $result->start_time = $league->start_time;
    $result->duration = $league->duration;
    $result->score = $league->base_score;
    $result->rank = rand(100000, 400000);
    $result->end_score = 0;
    $result->end_rank = 0;

    $strquery = "CALL `league_{$league->name}_create`(";
    $strquery .= intval($token->profile_id) . ", ";
    $strquery .= sql_quote($token->device_id) . ", ";
    $strquery .= intval($result->score) . ", ";
    $strquery .= intval($result->rank) . ")";

    if (queue_add($strquery))
    {
        send('ok', $result);
    }
    else send_error(sxerror::server_maintenance);
}
$db->close();


?>
