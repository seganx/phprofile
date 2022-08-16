<?php

function get_header_token(): string
{
	if (array_key_exists('HTTP_TOKEN', $_SERVER))
    	return $_SERVER['HTTP_TOKEN'];
	else
		return '';
}

function get_post_data()
{
    return file_get_contents('php://input');
}

function get_post_json()
{
    return json_decode(get_post_data());
}

function clamp(int $current, int $min, int $max): int
{
    return max($min, min($max, $current));
}

function hash_base(mixed $number, int $frombase, int $tobase): string
{
    $res = base_convert($number, $frombase, $tobase);
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

function send(string $msg, mixed $data)
{
    send_Headers();
    $res = new stdClass();
    $res->msg = $msg;
    $res->data = $data;
    echo json_encode($res);
}

function send_error(string $error)
{
    send_Headers();
    $res = new stdClass();
    $res->msg = $error;
    echo json_encode($res);
}

function parse_token(string $token)
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

function get_token(): mixed
{
    $token = get_header_token();
    return empty($token) ? null : parse_token($token);
}

function queue_add(string $msg): bool
{
    return file_put_contents(dirname(__FILE__) . '/queue/' . time() . '.txt', $msg . ';', FILE_APPEND | LOCK_EX);
}

function id_to_username(int $id) : string
{
    return base_convert(1000000 + $id, 10, 32);
}

function username_to_id(string $username): int
{
    return base_convert($username, 32, 10) - 1000000;
}

?>