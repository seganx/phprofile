<?php

require '_configs.php';
require '_database.php';

function dequeue($db)
{
    $queries = "";

    $cwdir = dirname(__FILE__) . "/queue";
    $files = scandir($cwdir);

    // validate number of files
    $files_count = count($files);
    if ($files_count < 3) return;

    // read all contents to string
    for ($i=2; $i < $files_count; $i++)
    {
        $path = $cwdir . "/" . $files[$i];
        $queries .= file_get_contents($path);
        unlink($path);
    }

    // verify query loaded
    if ($db != null && empty($queries) == false)
    {
        $db->multi_query($queries);
    }
}

$db = database::connect();
for ($i=0; $i < 60; $i++)
{
    dequeue($db);
    sleep(1);
}
$db->close();


?>