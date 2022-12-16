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
    $result = base_convert(18000 + $id, 10, 26);
    $len = strlen($result);
    for ($i = 0; $i < $len; $i++)
    {
    	switch($result[$i])
        {
            case '0': $result[$i] = 'q'; break;
            case '1': $result[$i] = 'r'; break;
            case '2': $result[$i] = 's'; break;
            case '3': $result[$i] = 't'; break;
            case '4': $result[$i] = 'u'; break;
            case '5': $result[$i] = 'v'; break;
            case '6': $result[$i] = 'w'; break;
            case '7': $result[$i] = 'x'; break;
            case '8': $result[$i] = 'y'; break;
            case '9': $result[$i] = 'z'; break;
        }
    }
    return $result;
}

function username_to_id(string $username): int
{
    $len = strlen($username);
    for ($i = 0; $i < $len; $i++)
    {
    	switch($username[$i])
        {
            case 'q': $username[$i] = '0'; break;
            case 'r': $username[$i] = '1'; break;
            case 's': $username[$i] = '2'; break;
            case 't': $username[$i] = '3'; break;
            case 'u': $username[$i] = '4'; break;
            case 'v': $username[$i] = '5'; break;
            case 'w': $username[$i] = '6'; break;
            case 'x': $username[$i] = '7'; break;
            case 'y': $username[$i] = '8'; break;
            case 'z': $username[$i] = '9'; break;
        }
    }
    return base_convert($username, 26, 10) - 18000;    
}

?>