<?php

function get_all_leagues()
{
    $res = array();
    
    $res[1] = new league(1, 0, 15, league::mode_weekly);
    $res[2] = new league(2, 0, 15, league::mode_weekly);
    $res[3] = new league(3, 0, 15, league::mode_weekly);
    $res[4] = new league(4, 0, 15, league::mode_weekly);
    
    return $res;
}

?>