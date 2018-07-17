<?php

namespace Core\Support;

class Collection
{
    private $collection = [];

    public function __construct(array $data = null)
    {
        if (! is_null($data)) {
            $this->collection = $data;
        }
    }

    public function add(array $data)
    {
        foreach ($data as $value) {
            $this->collection[] = $value;
        }

        return $this;
    }

    public function reset(array $data)
    {
        $this->collection = $data;

        return $this;
    }

    public function clear()
    {
        $this->collection = [];

        return $this;
    }

    // ========================= Get methods ========================
    public function all()
    {
        return $this->collection;
    }

    public function first()
    {
        return reset($this->collection);
    }

    public function last()
    {
        return end($this->collection);
    }

    // ========================= Formatting methods ========================
    public function keys()
    {
        return array_keys($this->collection);
    }

    public function values()
    {
        return array_values($this->collection);
    }

    public function toArray()
    {
        return $this->map(function ($item) {
            return is_a($item, self::class) ? $item->all() : $item;
        });
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    // ========================= Modification methods ========================
    public function push($data)
    {
        array_push($this->collection, $data);

        return $this;
    }

    public function map(callable $callable, array $array = null)
    {
        $args = [];
        $args[] = $callable;
        $args[] = $this->collection;

        $allArgs = func_get_args();

        array_shift($allArgs);

        foreach ($allArgs as $arg) {
            $args[] = $arg;
        }

        // $this->collection = call_user_func_array('array_map', $args);

        // return $this->collection;
        return call_user_func_array('array_map', $args);
    }
}