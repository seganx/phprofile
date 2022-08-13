<?php

require '_configs.php';

function get_header_token()
{
    return $_SERVER['HTTP_TOKEN'];
}

function get_post_data()
{
    return file_get_contents('php://input');
}

function get_post_json()
{
    return json_decode(get_post_data());
}

function clamp($current, $min, $max) 
{
    return max($min, min($max, $current));
}

function hash_base($number, $frombase, $tobase)
{
    $res = base_convert($number, $frombase, $tobase);
    //$res = base64_encode($number);
    $res = str_replace('+', '', $res);
    $res = str_replace('=', '', $res);
    $res = str_replace('/', '', $res);
    return $res;
}

function send_headers()
{
    header('Connection: close');
    header('Cache-Control: no-cache');
    header('Content-type: application/json');
}

function send($msg, $data)
{
    send_Headers();
    $res = new stdClass();
    $res->msg = $msg;
    $res->data = $data;
    echo json_encode($res);
}

function send_error($error)
{
    send_Headers();
    $res = new stdClass();
    $res->msg = $error;
    echo json_encode($res);
    
}

function parse_token($token)
{
    $parts = explode('_', $token);
    if (count($parts) != 2) return null;

    $crc32 = strval(crc32($parts[0] . configs::token_salt));
    if ($crc32 === $parts[1])
    {
        $json = base64_decode($parts[0]);
        return json_decode($json);
    }
    
    return null;
}

function get_token()
{
    $token = get_header_token();
    return empty($token) ? null : parse_token($token);
}

function queue_add($msg)
{
    return file_put_contents(dirname(__FILE__) . '/queue/' . time() . '.txt', $msg, FILE_APPEND | LOCK_EX);
}

function id_to_username($id)
{
    return base_convert(1000000 + $id, 10, 32);
}

function username_to_id($username)
{
    return base_convert($username, 32, 10) - 1000000;
}

?>