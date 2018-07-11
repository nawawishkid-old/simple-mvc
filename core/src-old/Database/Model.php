<?php

namespace Core\Database;

use Core\Database\DBManager as DB;
use Core\Support\Collection;
use Core\Support\Debugger;
use Core\Support\Traits\SQLComposer;

abstract class Model
{
    private $db;

    // protected static $table = [];

    private $selectedRecord;

    private $newRecord;

    private static $instances = [];

    public function __construct(string $table = null)
    {
        // self::checkCalledClassInstance();
        // DB = new DB();
        $this->selectedRecord = new Collection();
        $this->newRecord = new Collection();
    }

    // public function __callStatic(string $method, $arguments)
    // {
    //     var_dump('CALL STATIC!');
    //     $instance = new self();
    //     return \call_user_func_array([$instance, $method], $arguments);
    // }

    public function find(string $column, string $operator, $value)
    {
        self::checkCalledClassInstance();

        DB::select(['*'], self::getCalledClassInstance()->getTable())
                    ->where($column, $operator, $value);
        
        return DB;
    }

    protected function get()
    {
        $result = DB::fetch();
        self::getCalledClassInstance()->selectedRecord->reset($result);
        
        return self::getCalledClassInstance()->selectedRecord;
    }

    /**
     * Select records from database
     */
    public function select(array $columns)
    {
        DB::select($columns, self::getCalledClassInstance()->getTable());

        return DB;
    }

    /**
     * Insert new record into database
     */
    public function create(array $records = null)
    {
        if (! is_null($records)) {
            if (! \is_array($records[0])) {
                throw new \Exception("Error: Items of given array argument must be an array, " . gettype($records[0]) . " given.", 1);
                
            }

            foreach ($records as $record) {
                self::add($record);
            }
        }

        $columns = \array_keys($this->newRecord->first());
        $values = [];

        foreach ($this->newRecord->all() as $record) {
            foreach(\array_values($record) as $value) {
                $values[] = $value;
            }
        }

        // (new Debugger())->varDump($columns, "Insert columns");
        // (new Debugger())->varDump($values, "Insert values");

        DB::insert(self::getCalledClassInstance()->getTable(), $columns, $values);
        $result = DB::execute('insert_into');

        if (! $result) {
            throw new \Exception("Error: Could not insert data", 1);
            
        }
        // var_dump(DB::compose());
        // exit;
    }

    /**
     * Add single record to Model object, not to database.
     */
    public function add(array $record)
    {
        $this->newRecord->push($record);
    }

    public function update(array $updatedValues = null)
    {
        if (! \is_null($updatedValues)) {
            $values = $updatedValues;
        }

        // ควรให้ $values เป็น callable ได้ด้วย?
        DB::update(self::getCalledClassInstance()->getTable(), $values);

        return DB;
    }

    public function save()
    {
        // (new Debugger())->varDump(DB::baseKeywords, 'Base keywords');

        if (empty(DB::baseKeywords['update'])) {
            throw new \Exception("Error: Could not update record. You have to call update() method before call save() method", 1);
            
        }
        
        $result = DB::execute('update');

        if (! $result) {
            throw new \Exception("Error: Could not update data", 1);
            
        }
    }

    public function delete()
    {
        DB::deleteFrom(self::getCalledClassInstance()->getTable());
        // (new Debugger())->varDump(DB::compose('delete_from'));

        return DB;
    }

    public function confirmDelete()
    {
        $result = DB::execute('delete_from');

        if (! $result) {
            throw new \Exception("Error: Could not delete data", 1);
            
        }
    }

    /**
     * Get all records from database
     */
    public function all()
    {
        // (new Debugger())->varDump(\get_called_class(), 'Class');
        // exit;
        self::checkCalledClassInstance();
        // $calledClass = \get_called_class();
        // $instance = new $calledClass;
        // $instance->all()

        self::select(['*']);

        return self::get()->all();
    }

    /**
     * Format selected records to JSON
     */
    public function toJson()
    {
        return self::get()->toJson();
    }

    private function checkCalledClassInstance()
    {
        $calledClass = \get_called_class();

        if (empty(self::$instances[$calledClass])) {
            self::setCalledClassInstance($calledClass);
        }
    }

    private function getCalledClassInstance()
    {
        $calledClass = \get_called_class();
        $instance = self::$instances[$calledClass];
        // INFINITE LOOP HERE!

        if (empty($instance)) {
            self::setCalledClassInstance($calledClass);
        }

        return self::$instances[$calledClass];
    }

    private function setCalledClassInstance(string $className)
    {
        $calledClass = \get_called_class();
        $instance = new $calledClass;
        
        // $instance->selectedRecord = new Collection();
        // $instance->newRecord = new Collection();

        self::$instances[$calledClass] = $instance;
    }

    public function getTable()
    {
        return $this->table;
    }

    // private function getCalledClassTable()
    // {
    //     $calledClass = \get_called_class();
    //     $instance = new $calledClass;

    //     return $instance->table;
    // }
}