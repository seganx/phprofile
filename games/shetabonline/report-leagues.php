<?php

function load_all_files() : array
{
	$result = array();
    $cwdir = dirname(__FILE__) . '/cache';
    $files = scandir($cwdir);
    $files_count = count($files);
    if ($files_count < 3) return $result;
    for ($i=2; $i < $files_count; $i++)
    {
        $path = $cwdir . "/" . $files[$i];
		$content = file_get_contents($path);
		$json =  json_decode( $content );
        $result[$path] = $json;
    }
	return $result;
}

$records = load_all_files();

foreach ($records as $key => $list)
{
	echo "<table border='1' style='zoom:200%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>"; 
	echo "<tr style='font-weight: bold;'>" . $key . '</tr>';
	foreach ($list as $row) 
	{ 
		if (empty($row->nickname)) continue;
		echo "<tr>";  
		echo "<td width='10%' align='left'>" . $row->username . "</td>";
		echo "<td width='60%' align='left'>" . $row->nickname . "</td>";
		echo "<td width='20%' align='left'>" . $row->score . "</td>";
		echo "<td width='10%' align='left'>" . $row->avatar . "</td>";
		echo '</tr>';
	}

	echo "</table>";
}

?>
