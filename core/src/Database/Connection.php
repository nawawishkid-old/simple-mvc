<?php

namespace Core\Database;

use PDO;

class Connection
{
    /**
     * PDO instance
     */
    private $conn = null;

    /**
     * Database connection information.
     */
    private $connectionInfo = [
        'host' => '',
        'dbname' => '',
        'username' => '',
        'password' => ''
    ];

    /**
     * Close database connection on unset.
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connect to database.
     * 
     * @api
     * 
     * @return void
     */
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

    /**
     * Fetch data from database using given SQL statement.
     * 
     * @api
     * @param string $statement SQL statement.
     * 
     * @return array $rows Array of data from database.
     */
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

    /**
     * Close database connection.
     * 
     * @return void
     */
    private function close()
    {
        if (isset($this->conn)) {
            $this->conn = null;
        }
    }

    /**
     * Ensure that there is a connection between the app and the database
     */
    private function ensureConnection()
    {
        if (! isset($this->conn)) {
            $this->connect();
        }
    }
}