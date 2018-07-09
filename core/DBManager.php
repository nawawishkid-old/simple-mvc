<?php

namespace Core;

use Core\Traits\SQLComposer;
use Core\Config;
use \PDO;

class DBManager
{
    use SQLComposer;

    private static $conn = null;

    private static $connectionInfo = [
        'host' => '',
        'dbname' => '',
        'username' => '',
        'password' => ''
    ];

    // Connection
    public static function connect()
    {
        if (! Config::isLoaded('database')) {
            Config::loadModule('database');
        }

        $config = Config::get('database');
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$conn = new PDO($dsn, $config['username'], $config['password'], $opt);
    }

    public static function close()
    {
        if (isset(self::$conn)) {
            self::$conn = null;
        }
    }

    public function get()
    {
        if (! isset(self::$conn)) {
            self::connect();
        }

        // echo $this->compose();
        $statement = self::$conn->prepare($this->compose());
        $statement->execute($this->input);

        return $statement->fetch();
    }
}