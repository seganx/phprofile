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
$db->query("SELECT COUNT(DISTINCT profile_id) as 'c' FROM friends");
$res = $db->result->fetch_assoc();
$db->close();

echo "<table border='1' style='border-collapse: collapse;border-color: silver;'>";  
echo "<tr style='font-weight: bold;'>";  
echo "<td width='150' align='center'>Count</td>";  
echo "</tr>";
echo '<td>' . $res['c'] . '</td>';
echo '</tr>';
?>