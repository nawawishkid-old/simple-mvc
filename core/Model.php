<?php

namespace Core;

use Core\DBManager as DB;
use Core\Traits\SQLComposer;

class Model
{
    public $db;

    public function __construct()
    {
        $this->db = new DB();
    }
}