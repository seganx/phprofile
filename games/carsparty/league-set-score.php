<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';
require '_configs.php';

// validate token
$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

// validate user data
$userdata = get_post_json();
if ($userdata->hash !== md5("seganx_" . $userdata->score . "&" . $userdata->value . "#(" . $userdata->id))
{
    send_error(sxerror::invalid_params);
    exit();
}

// validate league id
$league = get_all_leagues()[$userdata->id];
if ($league == null || $league->max_value < $userdata->value)
{
    send_error(sxerror::invalid_params);
    exit();
}

// fast response if score is base score
if ($userdata->score == $league->base_score)
{
    $finalscore = intval($league->base_score + $userdata->value);
    queue_add("INSERT INTO league (profile_id, league_id, score) VALUES ('$token->profile_id','$userdata->id', '$finalscore') ON DUPLICATE KEY UPDATE score=$finalscore;");
    send("ok", $finalscore);
    exit();
}

// validate current score value with database
$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

$dbscore = null;
$db->query("SELECT score FROM league WHERE profile_id='$token->profile_id' AND league_id='$userdata->id'");
if ($db->has_result())
{
    $dbscore = $db->result->fetch_assoc()['score'];
}
$db->close();

if (empty($dbscore))
{
    $finalscore = intval($league->base_score + $userdata->value);
    queue_add("INSERT INTO league (profile_id, league_id, score) VALUES ('$token->profile_id','$userdata->id', '$finalscore');");
    send("ok", $finalscore);
}
else
{
    if ($userdata->score == $dbscore)
    {
        $finalscore = intval($userdata->score + $userdata->value);
        queue_add("UPDATE league SET score=$finalscore WHERE profile_id='$token->profile_id' AND league_id='$userdata->id';");
        send("ok", $finalscore);
    }
    else send_error(sxerror::invalid_params);
}

?>