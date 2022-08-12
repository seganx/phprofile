<?php

require '_classes.php';
require '_configs.php';
require '_utilities.php';

$token = get_token();
if ($token == null)
{
    send_error(sxerror::invalid_token);
    exit();	
}

$games = get_all_games();
$game = $games[$token->game_id];

$userjson = get_post_json();
if ($userjson->provider == "bazaar")
{
    $filename = dirname(__FILE__) . "/cache/cafebazaar_" . $game->bazaar . ".txt";
    $accessdata = json_decode( file_get_contents($filename) );
    send( "ok", $accessdata->access_token );
}
else if ($userjson->provider == "myket")
{
    send( "ok", "8663c45d-a736-44da-8c52-32d83ddaea9e" );
}
else if ($userjson->provider == "gateway")
{

}
else 
{
    send_error( sxerror::invalid_params );
}

?>