<?php

namespace Core\Support;

class Collection
{
    /**
     * @property array Collection array.
     */
    private $collection = [];

    /**
     * Add data to collection array on instantiation.
     * 
     * @param array $data Data array.
     * 
     * @return void
     */
    public function __construct(array $data = null)
    {
        if (! is_null($data)) {
            $this->collection = $data;
        }
    }

    /**
     * Push each item of given array into the collection array.
     * 
     * @param array $data Data array.
     * 
     * @return $this
     */
    public function add(array $data)
    {
        foreach ($data as $value) {
            $this->collection[] = $value;
        }

        return $this;
    }

    /**
     * Set the collection to be the given array.
     * 
     * @param array $data Data array.
     * 
     * @return $this
     */
    public function reset(array $data)
    {
        $this->collection = $data;

        return $this;
    }

    /**
     * Abolish the collection.
     * 
     * @return $this
     */
    public function clear()
    {
        $this->collection = [];

        return $this;
    }

    // ========================= Get methods ========================
    /**
     * Return specific item of the collection.
     * 
     * @param string $name Key of an item in the collection array.
     * 
     * @return mixed Item of the collection.
     */
    public function __get($name)
    {
        return $this->collection[$name];
    }

    /**
     * Return the full collection.
     * 
     * @return array The collection.
     */
    public function all()
    {
        return $this->collection;
    }

    /**
     * Return first item of collection.
     * 
     * @return mixed First item of collection array.
     */
    public function first()
    {
        return reset($this->collection);
    }

    /**
     * Return last item of collection.
     * 
     * @return mixed Last item of collection array.
     */
    public function last()
    {
        return end($this->collection);
    }

    // ========================= Formatting methods ========================
    /**
     * Apply array_keys.
     * 
     * @return array Array of collection keys.
     */
    public function keys()
    {
        return array_keys($this->collection);
    }

    /**
     * Apply array_values.
     * 
     * @return array Array of collection values.
     */
    public function values()
    {
        return array_values($this->collection);
    }

    /**
     * Return collection array instead of Collection instance.
     * 
     * @return array Collection array.
     */
    public function toArray()
    {
        return $this->map(function ($item) {
            return is_a($item, self::class) ? $item->all() : $item;
        });
    }

    /**
     * Return collection in JSON format.
     * 
     * @return string JSON.
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    // ========================= Modification methods ========================
    /**
     * Apply array_push.
     * 
     * @param mixed $data Data to be pushed.
     * 
     * @return array Updated array.
     */
    public function push($data)
    {
        array_push($this->collection, $data);

        return $this;
    }

    /**
     * Appy array_map.
     * 
     * @param callable $callable Callback for array_map.
     * @param array $array Additional array.
     * 
     * @return array Mapped array.
     */
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
        
        return call_user_func_array('array_map', $args);
    }
}