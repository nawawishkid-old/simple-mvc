<?php

namespace Core\Database;

// use Core\Support\Traits\SQLComposer;
use Core\Support\Debugger;
use Core\Config;
use \PDO;

class DBManager extends SQLComposer
{
    // use SQLComposer;

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
        self::connectionReady();

        $keyword = \is_null($baseKeyword) ? self::$selectedBaseKeyword : $baseKeyword;
        $statement = self::compose($keyword);

        // (new Debugger())->varDump($keyword, "DBManager::execute() keyword");
        (new Debugger())->varDump($statement, "DBManager::execute() statement");
        (new Debugger())->varDump(self::$inputs, "DBManager::execute() \self::$inputs");

        $stmt = self::$conn->prepare($statement);

        $stmtInputs = ($keyword === 'select') ? null : self::$inputs[$keyword];

        $stmt->execute($stmtInputs);

        self::resetStatement();
        // var_dump(self::$inputs[$keyword]);

        return $stmt;
    }

    public function fetch()
    {
        self::connectionReady();

        $stmt = self::execute('select');

        $results = [];

        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }

        return $results;
    }

    private static function connectionReady()
    {
        if (! isset(self::$conn)) {
            self::connect();
        }
    }
}