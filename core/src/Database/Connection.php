<?php

namespace Core\Database;

use PDO;

class Connection
{
    private $conn = null;

    private $connectionInfo = [
        'host' => '',
        'dbname' => '',
        'username' => '',
        'password' => ''
    ];

    public function __construct()
    {
        
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connect()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $this->conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $opt);
    }
    
    public function send()
    {

    }

    public function fetch($statement)
    {
        $this->ensureConnection();

        $stmt = $this->conn->query($statement);

        $rows = [];

        foreach ($stmt as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function close()
    {
        if (isset($this->conn)) {
            $this->conn = null;
        }
    }

    private function ensureConnection()
    {
        if (! isset($this->conn)) {
            $this->connect();
        }
    }
}