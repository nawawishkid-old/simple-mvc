<?php

namespace Core\Database;

use Core\Database\DBManager as DB;
use Core\Support\Traits\SQLComposer;
use Core\Support\Collection;

class Model
{
    private $db;

    private $table;

    private $selectedRecord;

    private $newRecord;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->db = new DB();
        $this->selectedRecord = new Collection();
        $this->newRecord = new Collection();
    }

    public function find(string $column, string $operator, $value)
    {
        $this->db->select(['*'], $this->table)
                    ->where($column, $operator, $value);
        
        return $this->db;
    }

    protected function get()
    {
        $result = $this->db->fetch();
        $this->selectedRecord->reset($result);
        
        return $this->selectedRecord;
    }

    /**
     * Select records from database
     */
    public function select(array $columns)
    {
        $this->db->select($columns, $this->table);

        return $this->db;
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
                $this->add($record);
            }
        }

        $columns = \array_keys($this->newRecord->first());
        $values = [];

        foreach ($this->newRecord->all() as $record) {
            foreach(\array_values($record) as $value) {
                $values[] = $value;
            }
        }

        // var_dump($values);

        $this->db->insert($this->table, $columns, $values);
        $result = $this->db->execute();

        if (! $result) {
            throw new Exception("Error: Could not insert data", 1);
            
        }
        // var_dump($this->db->compose());
        // exit;
    }

    /**
     * Add single record to Model object, not to database.
     */
    public function add(array $record)
    {
        $this->newRecord->push($record);

        return $this;
    }

    public function update()
    {
        $columns = \array_keys($this->selectedRecord);
        $values = \array_values($this->selectedRecord);

        // ควรให้ $values เป็น callable ได้ด้วย?
        $this->db->insert($this->table, $columns, $values);
    }

    /**
     * Get all records from database
     */
    public function all()
    {
        return $this->get()->all();
    }

    /**
     * Format selected records to JSON
     */
    public function toJson()
    {
        return $this->get()->toJson();
    }
}