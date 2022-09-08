<?php

header('Cache-Control: no-cache');
header('Content-type: application/json');

$time = time();
$totalDays = (int)ceil($time / 86400) - 93;
$ydays = $totalDays % 365;
$mdays = ($ydays < 187) ? ($ydays % 31) : ($ydays - 186) % 30;
$wdays = (($ydays + 2) % 7);

$r_ydays = 364 - $ydays;
$r_mdays = ($ydays < 187) ? (30 - $mdays) : (29 - $mdays);
$r_wdays = 6 - $wdays;


echo '{"msg":"ok","data":{"time":'.$time.',"year_days":'.$ydays.',"month_days":'.$mdays.',"week_days":'.$wdays.',"year_days_left":'.$r_ydays.',"month_days_left":'.$r_mdays.',"week_days_left":'.$r_wdays.'}}'

?>