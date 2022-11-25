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

    public function multi_query(string $query)
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

    public function has_result(): bool
    {
        return $this->result != null && $this->result->num_rows > 0;
    }

    public function no_result(): bool
    {
        return $this->result == null || $this->result->num_rows == 0;
    }

    public function error()
    {
        return $this->conn->error;
    }

    public function insert_id(): int
    {
        return $this->conn->insert_id;
    }

    public function affected_rows()
    {
        return $this->conn->affected_rows;
    }

    private function close_result()
    {
        if ($this->result == null) return;
		$this->result->close();
        $this->result = null;
		while ($this->conn->more_results() && $this->conn->next_result());
    }

    public function close()
    {
        $this->close_result();
        if ($this->conn != null) $this->conn->close();
    }

    public static function connect()
    {
        $conn = new mysqli(configs::database_servername, configs::database_username, configs::database_password, configs::database_name);

        if ($conn->connect_error)
        {
            error_log("Can not connect to database du to {$conn->connect_error}");
            return null;
        }
        else
        {
            return new database($conn);
        }
    }
}

?>