<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../_configs.php';
require '../_database.php';

function dequeue($db)
{
    $queries = "";

    $cwdir = dirname(__FILE__) . "/../queue";
    $files = scandir($cwdir);

    // validate number of files
    $files_count = count($files);
    if ($files_count < 3) return;

    // read all contents to string
    for ($i=2; $i < $files_count; $i++)
    {
        $path = $cwdir . "/" . $files[$i];
		try
		{
			$queries .= file_get_contents($path);
			unlink($path);
		}
		catch (Exception $e) { }
    }

    // verify query loaded
    if (empty($queries) == false)
    {
		try
		{
    	    $db->multi_query($queries);
		}
		catch (Exception $e) 
		{
			$db->close();
			echo "db->multi_query : {$queries} \n\r";
			echo  $e->getMessage();
		}
    }
}

$db = database::connect();
for ($i=0; $i < 60; $i++)
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
			echo  $e->getMessage();
		}
	}
	sleep(1);
}
$db->close();

?>
