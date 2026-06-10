<?php

require '_errors.php';
require '_configs.php';
require '_database.php';
require '_utilities.php';


$days = 9999;
if (isset($_GET['days']))
{
    $days = intval($_GET['days']);
}
$current_time = time();
$remained_time = $current_time % 86400;
$valid_time = $current_time - $remained_time;
$start_time =  $valid_time - ($days * 86400) + 86400;

$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$news = array();
$db->query("SELECT DAY(`join_date`) as day, COUNT(*) as count FROM `profile` WHERE UNIX_TIMESTAMP(`join_date`) > {$start_time} GROUP BY DATE_FORMAT(`join_date`, '%Y%m%d') ORDER BY DATE_FORMAT(`join_date`, '%Y%m%d') DESC");
if ($db->has_result())
{
	while($r = $db->result->fetch_assoc())
	{
    	$news[] = array($r['day'], $r['count']);
	}
}

$versions = array();
$db->query("SELECT `client_id`, COUNT(*) as count FROM `profile` WHERE UNIX_TIMESTAMP(`join_date`) > {$start_time} GROUP BY `client_id` ORDER BY `client_id` DESC");
if ($db->has_result())
{
	while($r = $db->result->fetch_assoc())
	{
    	$versions[] = array($r['client_id'], $r['count']);
	}
}
$db->close();


echo "<table border='1' style='zoom:300%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='50%' align='left'>Shetab: Days {$days}</td>";
echo "<td width='50%' align='left'>Count</td>";
echo "</tr>";

foreach ($news as $row) 
{ 
    echo '<td align="right">' . $row[0] . '</td>';
	echo '<td align="right">' . $row[1] . '</td>';
    echo '</tr>';
}
echo "</table>";

echo "<table border='1' style='zoom:300%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>";  
echo "</tr><td></td>";
echo "<tr style='font-weight: bold;'>";  
echo "<td width='40%' align='left'>Version</td>";
echo "<td width='20%' align='left'>Count</td>";
echo "</tr>";

foreach ($versions as $row) 
{ 
	echo '<td align="left">' . $row[0] . '</td>';
    echo '<td align="right">' . $row[1] . '</td>';
    echo '</tr>';
}
echo "</table>";

?>
