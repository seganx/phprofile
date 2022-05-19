<?php

class sxerror
{
    const server_maintenance = "server_maintenance";
    const invalid_token = "invalid_token";
    const invalid_params = "invalid_params";
    const account_transfered = "account_transfered";
}

class league
{
    public $id = 0;
    public $base_score = 1000;
    public $max_value = 30;
    public $start_day = 0;
    public $duration = 1;
    
    function __construct ($i, $b, $m, $s, $d)
    {
        $this->id = $i;
        $this->base_score = $b;
        $this->max_value = $m;
        $this->start_day = $s;
        $this->duration = $d;
    }
}

?>