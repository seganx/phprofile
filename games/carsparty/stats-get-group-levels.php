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
    $db->query("SELECT level,COUNT(*) FROM stats WHERE version >= $ver GROUP BY level");
}
else 
{    
    $db->query("SELECT level,COUNT(*) FROM stats GROUP BY level");
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

$arraycount = count($rows);

for ($i=0; $i < $arraycount; $i++) 
{ 
    # code...
    $rows[$i]['sum'] = $rows[$i]['COUNT(*)'];
}

$tmp = 0;
for ($i = $arraycount - 2; $i >= 0; $i--) 
{ 
    # code...
    $tmp = $rows[$i]['sum'] + $rows[$i + 1]['sum'];
    $rows[$i]['sum'] = $tmp;
}

echo "<table border='1' style='border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='50' align='center'>Level</td>";  
echo "<td width='50' align='center'>Sum</td>";  
echo "<td width='50' align='center'>Count</td>";  
echo "</tr>";
foreach ($rows as $row) 
{ 
    $level = intval($row['level']) + 1;
    echo '<td>' . $level . '</td>';
    echo '<td>' . $row['sum'] . '</td>';
    echo '<td>' . $row['COUNT(*)'] . '</td>';
    echo '</tr>';
}

?>