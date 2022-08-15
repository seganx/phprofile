<?php

require '_configs.php';
require '_database.php';

function dequeue()
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
    if (empty($queries) == false)
    {
        $db = database::connect();
        if ($db != null)
        {
            $db->multi_query($queries);
            $db->close();
        }
    }
}

for ($i=0; $i < 60; $i++)
{
    sleep(1);
    dequeue();
}


?>