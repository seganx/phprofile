<?php
header('Cache-Control: no-cache');
header('Content-type: application/json');

echo '{\"msg\":\"ok\", \"data\":'. $_SERVER['REMOTE_ADDR'] . '}';
?>