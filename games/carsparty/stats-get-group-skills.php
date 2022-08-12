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
    $db->query("SELECT skill,COUNT(*) FROM stats WHERE version >= $ver GROUP BY skill");
}
else 
{    
    $db->query("SELECT skill,COUNT(*) FROM stats GROUP BY skill");
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
echo "<td width='50' align='center'>Skill</td>";  
echo "<td width='50' align='center'>Count</td>";  
echo "</tr>";
foreach ($rows as $row) 
{ 
    echo '<td>' . $row['skill'] . '</td>';
    echo '<td>' . $row['COUNT(*)'] . '</td>';
    echo '</tr>';
}

?>