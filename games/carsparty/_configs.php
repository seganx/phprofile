<?php

function get_all_bazaar()
{
    $res = array();

    // add parseh games
    {
        $parsehgames = new stdClass();
        $parsehgames->client_id = 't2Y2MwDaN5IHp38g48Cxb7GIf2mGi0VzsGLeepZt';
        $parsehgames->client_secret = 'noaFrFRsbC6DNYf2MpRRhNoij1ztgc7SWuBDHvMB7CpgQEBIqGmQOCtJ7Fm8';
        $parsehgames->redirect_uri = 'http://seganx.ir/games/api/update-bazaar.php?bazaar=parsehgames';
        $res['parsehgames'] = $parsehgames;
    }

    // add novin games
    {
        $novingames = new stdClass();
        $novingames->client_id = 'cYIZI3uCAxFfi0GYe4UCVjmxJx79LnJ7iM4FGRoj';
        $novingames->client_secret = 'JSUJANpYtPAh6Zrmtp5ud7Rsl4o3b39YQr1wGTagkjX81keZlHwTwf9bq0nB';
        $novingames->redirect_uri = 'http://seganx.ir/games/api/update-bazaar.php?bazaar=novingames';
        $res['novingames'] = $novingames;
    }

    return $res;
}

function get_all_games()
{
    $res = array();

    // add speed online
    {
        $speedonline = new stdClass();
        $speedonline->id = 50001;
        $speedonline->bazaar = 'novingames';
        $speedonline->package_name = 'com.parsehgames.racer';
        $res[$speedonline->id] = $speedonline;
    }

    // add ameza
    {
        $ameza = new stdClass();
        $ameza->id = 469536763;
        $ameza->bazaar = 'parsehgames';
        $ameza->package_name = 'com.parsehgames.ameza';
        $res[$ameza->id] = $ameza;
    }

    // add shetab
    {
        $shetab = new stdClass();
        $shetab->id = 7438220;
        $shetab->bazaar = 'parsehgames';
        $shetab->package_name = 'com.parsehgames.shetab';
        $res[$shetab->id] = $shetab;
    }

    return $res;
}

function is_game_valid($game_id)
{
    switch ($game_id)
    {
        case 50001 : return true; // speed online
        case 469536763 : return true; // ameza
        case 7438220 : return true; // shetab
        default: return false;
    }
}

function get_all_leagues()
{
    $res = array();
    
    // league(id, base score, max value, start day, duration)

    // ameza
    $res[4695001] = new league(4695001, 0, 9999, 0, 1);   // damgarman league
    $res[4695002] = new league(4695002, 0, 9999, 5, 7);
    $res[4695003] = new league(4695003, 0, 9999, 19, 30);

    // shetab
    $res[7438221] = new league(7438221, 0, 15, 0, 1);
    $res[7438222] = new league(7438222, 0, 15, 5, 7);
    $res[7438223] = new league(7438223, 0, 15, 19, 30);
    
    return $res;
}

?>