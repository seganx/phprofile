<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../_configs.php';
require '../_database.php';
require '../_utilities.php';

const queue_batch_limit = 100;

function queue_move_to(string $path, string $directory): string
{
    ensure_dir($directory);
    $target = $directory . '/' . basename($path);
    if (file_exists($target))
        $target = $directory . '/' . time() . '-' . basename($path);

    return rename($path, $target) ? $target : '';
}

function queue_process_file($db, string $path): bool
{
    $processing = queue_move_to($path, queue_dir('processing'));
    if ($processing === '') return false;

    $query = file_get_contents($processing);
    $ok = $query !== false && $db->multi_query($query) !== false;

    if ($ok)
        unlink($processing);
    else
        queue_move_to($processing, queue_dir('failed'));

    return $ok;
}

function dequeue($db)
{
    ensure_dir(queue_dir('pending'));
    ensure_dir(queue_dir('processing'));
    ensure_dir(queue_dir('failed'));

    $processed = 0;
    $pending = glob(queue_dir('pending') . '/*.sql');
    if ($pending === false) return;

    sort($pending);
    $count = count($pending);
    for ($i = 0; $i < $count; $i++)
    {
        queue_process_file($db, $pending[$i]);
        $processed++;
        if ($processed >= queue_batch_limit) return;
    }
}

$lock = fopen(__FILE__ . '.lock', 'c');
if ($lock == false || !flock($lock, LOCK_EX | LOCK_NB))
    exit();

$db = database::connect();
for ($i = 0; $i < 60; $i++)
{
    if ($db == null || $db->is_null())
    {
        $db = database::connect();
    }
    else
    {
        try
        {
            dequeue($db);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }
    sleep(1);
}

if ($db != null && !$db->is_null())
    $db->close();

flock($lock, LOCK_UN);
fclose($lock);

?>
