<?php

require '_classes.php';
require '_database.php';
require '_utilities.php';

$db = database::connect();
if ($db == null) 
{
    send_error(sxerror::server_maintenance);
    exit();
}

if (isset($_GET['ver']))
{
    $ver = intval($_GET['ver']);
    $db->query("SELECT profile_id,token,sku,time FROM purchases WHERE version=$ver ORDER BY time DESC");
}
else 
{    
    $db->query("SELECT profile_id,token,sku,time,version FROM purchases ORDER BY time DESC");
}

$rows = array();
if ($db->has_result())
{
    while($r = $db->result->fetch_assoc())
    {
        $rows[] = $r;
    }
}
$db->close();


echo "<table border='1' style='border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='70' align='center'>Profile</td>";  
echo "<td width='150' align='center'>Token</td>";
echo "<td width='130' align='center'>Sku</td>"; 
echo "<td width='150' align='center'>Time</td>"; 
if (isset($_GET['ver']) == false)
{
    echo "<td width='50' align='center'>Ver</td>"; 
}
echo "</tr>";
foreach ($rows as $row) 
{ 
    echo '<td>' . $row['profile_id'] . '</td>';
    echo '<td>' . $row['token'] . '</td>';
    echo '<td>' . $row['sku'] . '</td>';
    echo '<td>' . $row['time'] . '</td>';
    if (isset($_GET['ver']) == false)
    {
        echo '<td>' . $row['version'] . '</td>';
    }
    echo '</tr>';
}

?>