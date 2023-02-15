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


$db = database::connect();
if ($db == null)
{
    send_error(sxerror::server_maintenance);
    exit();
}

$revenues = array();
$db->query("SELECT DAY(timestamp), COUNT(*), SUM(price) FROM `purchases` GROUP BY DATE_FORMAT(timestamp, '%Y%m%d') DESC LIMIT {$days}");
if ($db->has_result())
{
	while($r = $db->result->fetch_assoc())
	{
    	$revenues[] = array($r['DAY(timestamp)'], $r['COUNT(*)'], $r['SUM(price)']);
	}
}

$skus = array();
$db->query("SELECT sku, COUNT(*), SUM(price) FROM `purchases` GROUP BY sku ORDER BY COUNT(*) DESC");
if ($db->has_result())
{
	while($r = $db->result->fetch_assoc())
	{
    	$skus[] = array($r['sku'], $r['COUNT(*)'], $r['SUM(price)']);
	}
}
$db->close();

$total = 0;
foreach ($revenues as $row) 
{
	$total += $row[2];
}

echo "<table border='1' style='zoom:300%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='40%' align='left'>Total Revenue</td>";  
echo "<td width='60%' align='right'>" . number_format($total) . "</td>";
echo "</table>";
echo "<table border='1' style='zoom:300%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='40%' align='left'>Day</td>";
echo "<td width='20%' align='left'>Count</td>";
echo "<td width='40%' align='left'>Revenue</td>";
echo "</tr>";

foreach ($revenues as $row) 
{ 
    echo '<td align="right">' . $row[0] . '</td>';
	echo '<td align="right">' . $row[1] . '</td>';
    echo '<td align="right">' . number_format($row[2]) . '</td>';
    echo '</tr>';
}
echo "</table>";

echo "<table border='1' style='zoom:300%; width:99%; height:100% border-collapse: collapse;border-color: silver;border-collapse: collapse;border-color: silver;'>";  
echo "</tr><td></td>";
echo "<tr style='font-weight: bold;'>";  
echo "<td width='40%' align='left'>Sku</td>";
echo "<td width='20%' align='left'>Count</td>";
echo "<td width='40%' align='left'>Revenue</td>";
echo "</tr>";

foreach ($skus as $row) 
{ 
	echo '<td align="left">' . $row[0] . '</td>';
    echo '<td align="right">' . $row[1] . '</td>';
    echo '<td align="right">' . number_format($row[2]) . '</td>';
    echo '</tr>';
}
echo "</table>";

?>
