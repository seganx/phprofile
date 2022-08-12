<?php

require '_classes.php';
require '_configs.php';
require '_utilities.php';

$userjson= get_post_json();
$userjson->game_id = intval($userjson->game_id);
if (is_game_valid($userjson->game_id) == false)
{
    send_error(sxerror::invalid_params);
    exit();
}

$games = get_all_games();
$game = $games[$userjson->game_id];

$filename = dirname(__FILE__) . "/cache/cafebazaar_" . $game->bazaar . ".txt";
$accessdata = json_decode( file_get_contents($filename) );
$ch = curl_init( "https://pardakht.cafebazaar.ir/devapi/v2/api/validate/$game->package_name/inapp/$userjson->sku/purchases/$userjson->token/" );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array("Authorization: $accessdata->access_token") );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$result = curl_exec( $ch );
curl_close( $ch );

$payload = json_decode($result)->developerPayload;
if (empty($payload))
{
    send( sxerror::server_maintenance, $result );
}
else
{
    send( "ok", $payload );
}

?>