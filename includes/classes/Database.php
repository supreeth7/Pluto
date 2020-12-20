<?php

require_once "./config.php";

class Database
{
    public $con = null;

    public function __construct()
    {
        $this->con = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

        if ($this->con->connect_error) {
            die("Connection error: ".$this->con->connect_errror);
        }
    }

    public function __destruct()
    {
        if ($this->con !=null) {
            $this->con->close();
            $this->con = null;
        }
    }
}
