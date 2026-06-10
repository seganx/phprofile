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
        if ($res instanceof mysqli_result)
            $this->result = $res;
        return $res;
    }

    public function multi_query(string $query)
    {
        $this->close_result();
        $res = $this->conn->multi_query($query);
        if ($res === false)
            return false;

        do
        {
            if ($tmp = $this->conn->store_result())
                $tmp->free();

            if ($this->conn->errno)
                return false;

            if (!$this->conn->more_results())
                break;
        }
        while ($this->conn->next_result());

        return $this->conn->errno == 0;
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

    public function escape(string $value): string
    {
        return $this->conn->real_escape_string($value);
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
		$this->conn = null;
    }

	public function is_null()
	{
		return $this->conn == null;
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
            $conn->set_charset('utf8mb4');
            return new database($conn);
        }
    }
}

?>
