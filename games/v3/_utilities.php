<?php

function app_root_dir(): string
{
    return __DIR__;
}

function get_header_token(): string
{
    if (array_key_exists('HTTP_TOKEN', $_SERVER))
        return $_SERVER['HTTP_TOKEN'];
    return '';
}

function get_post_data()
{
    return file_get_contents('php://input');
}

function get_post_json()
{
    $json = json_decode(get_post_data());
    return $json == null ? new stdClass() : $json;
}

function clamp(int $current, int $min, int $max): int
{
    return max($min, min($max, $current));
}

function hash_base($number, int $frombase, int $tobase): string
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

function send(string $msg, $data)
{
    send_headers();
    $res = new stdClass();
    $res->msg = $msg;
    $res->data = $data;
    echo json_encode($res);
}

function send_error(string $error)
{
    send_headers();
    $res = new stdClass();
    $res->msg = $error;
    echo json_encode($res);
}

function create_token(object $payload): string
{
    $body = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $body, configs::token_salt);
    return $body . '_' . $signature;
}

function parse_token(string $token)
{
    $parts = explode('_', $token, 2);
    if (count($parts) != 2) return null;

    $body = $parts[0];
    $signature = $parts[1];
    $hmac = hash_hmac('sha256', $body, configs::token_salt);

    if (!hash_equals($hmac, $signature)) return null;

    $json = base64_decode($body, true);
    if ($json === false) return null;

    $payload = json_decode($json);
    if ($payload == null || !isset($payload->game_id) || $payload->game_id != configs::game_id) return null;
    if (!isset($payload->profile_id) || !isset($payload->device_id)) return null;

    return $payload;
}

function get_token()
{
    $token = get_header_token();
    return empty($token) ? null : parse_token($token);
}

function ensure_dir(string $path): bool
{
    return is_dir($path) || mkdir($path, 0755, true);
}

function queue_base_dir(): string
{
    return app_root_dir() . '/queue';
}

function queue_dir(string $name): string
{
    return queue_base_dir() . '/' . $name;
}

function queue_job_id(): string
{
    try
    {
        return time() . '-' . bin2hex(random_bytes(8));
    }
    catch (Exception $e)
    {
        return time() . '-' . str_replace('.', '', uniqid('', true));
    }
}

function queue_add(string $sql): bool
{
    $base_dir = queue_base_dir();
    $tmp_dir = queue_dir('tmp');
    $pending_dir = queue_dir('pending');

    if (!ensure_dir($base_dir) || !ensure_dir($tmp_dir) || !ensure_dir($pending_dir))
        return false;

    $id = queue_job_id();
    $tmp_file = $tmp_dir . '/' . $id . '.tmp';
    $pending_file = $pending_dir . '/' . $id . '.sql';
    $content = trim($sql);
    if (empty($content)) return false;

    $content .= ";\n";

    if (file_put_contents($tmp_file, $content, LOCK_EX) === false)
        return false;

    if (!rename($tmp_file, $pending_file))
    {
        @unlink($tmp_file);
        return false;
    }

    return true;
}

function sql_escape(string $value): string
{
    $value = str_replace("\\", "\\\\", $value);
    $value = str_replace("\0", "\\0", $value);
    $value = str_replace("\n", "\\n", $value);
    $value = str_replace("\r", "\\r", $value);
    $value = str_replace("'", "\\'", $value);
    $value = str_replace('"', '\\"', $value);
    $value = str_replace(chr(26), "\\Z", $value);
    return $value;
}

function sql_quote($value): string
{
    return "'" . sql_escape((string)$value) . "'";
}

function sql_int($value): int
{
    return intval($value);
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
