<?php

namespace Core\Database;

use Core\Support\Traits\SQLComposer;
use Core\Support\Debugger;
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

    public function execute(string $baseKeyword = null)
    {
        $this->connectionReady();

        $keyword = \is_null($baseKeyword) ? $this->selectedBaseKeyword : $baseKeyword;
        $statement = $this->compose($keyword);

        (new Debugger())->varDump($this->inputs);

        $stmt = self::$conn->prepare($statement);
        $stmt->execute($this->inputs[$keyword]);

        $this->resetStatement();
        // var_dump($this->inputs[$keyword]);

        return $stmt;
    }

    public function fetch()
    {
        $this->connectionReady();

        $stmt = $this->execute('select');

        $results = [];

        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }

        return $results;
    }

    private function connectionReady()
    {
        if (! isset(self::$conn)) {
            self::connect();
        }
    }
}