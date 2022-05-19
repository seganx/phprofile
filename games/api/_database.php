<?php

class database
{
    public $conn = null;
    public $result = null;

    private function __construct($dbconn)
    {
        $this->conn = $dbconn;
    }

    public function query(string $query)
    {
        $this->close_result();
        $res = $this->conn->query($query);
        if (filter_var($res, FILTER_VALIDATE_BOOLEAN) == false)
            $this->result = $res;
        return $res;
    }

    public function multi_query(string $query) : bool
    {
        $this->close_result();
        $res = $this->conn->multi_query($query);   
        do 
        {
            if ($tmp = $this->conn->store_result())
                $tmp->free();
        } 
        while ($this->conn->more_results() && $this->conn->next_result());
        return $res;
    }

    public function has_result() : bool
    {
        return $this->result != null && $this->result->num_rows > 0;
    }

    public function no_result() : bool
    {
        return $this->result == null || $this->result->num_rows == 0;
    }
    
    public function error() : string
    {
        return $this->conn->error;
    }

    public function insert_id() : int
    {
        return $this->conn->insert_id;
    }

    private function close_result()
    {
        if ($this->result == null) return;
        $this->result->free();
        $this->result = null;
    }

    public function close()
    {
        $this->close_result();
        if ($this->conn != null) $this->conn->close();
    }
    
    public static function connect()
    {

        // create new database
        $conn = new mysqli($servername, $username, $password, $databasename);
        if ($conn->connect_error)
        {
            error_log("Can not connect to database du to $conn->connect_error");
            return null;
        }
        else
        {
            return new database($conn);
        }
    }
}

?>