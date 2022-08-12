<?php

require '_classes.php';
require '_configs.php';
require '_utilities.php';

function get_login_post_data($bazaar, $code)
{
    return "grant_type=authorization_code&code=$code&client_id=$bazaar->client_id&client_secret=$bazaar->client_secret&redirect_uri=$bazaar->redirect_uri";
}

function get_refresh_post_data($bazaar, $refresh_token)
{
    return "grant_type=refresh_token&client_id=$bazaar->client_id&client_secret=$bazaar->client_secret&refresh_token=$refresh_token";
}

function url_post($data)
{
    $ch = curl_init( 'https://pardakht.cafebazaar.ir/devapi/v2/auth/token/' );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec( $ch );
    curl_close( $ch );
    return json_decode($res);
}

///////////////////////////////////////////////////////
//  IMPLEMENTATION
///////////////////////////////////////////////////////
$bazaars = get_all_bazaar();

if (isset($_GET['code']) && isset($_GET['bazaar']))  // login
{    
    $code = $_GET['code'];
    $bazaar = $_GET['bazaar'];
    $filename = dirname(__FILE__) . "/cache/cafebazaar_" . $bazaar . ".txt";
    $result = url_post( get_login_post_data( $bazaars[$bazaar], $code ) );
    if ( empty($result->access_token) == false )
        file_put_contents($filename, json_encode($result), FILE_TEXT | LOCK_EX);
    send("ok", $result);
}
else // refresh
{    
    foreach ($bazaars as $key => $bazaar) 
    {
        $filename = dirname(__FILE__) . "/cache/cafebazaar_" . $key . ".txt";
        if ( file_exists( $filename ) == false ) continue;

        $accessdata = json_decode( file_get_contents( $filename ) );
        $result = url_post( get_refresh_post_data($bazaar, $accessdata->refresh_token) );
        if ( empty( $result->access_token ) == false )
        {
            $accessdata->access_token = $result->access_token;
            file_put_contents($filename, json_encode($accessdata), FILE_TEXT | LOCK_EX);
        }       
    }
}
?>