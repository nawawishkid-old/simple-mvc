<?php

namespace Core;

use Core\Output\View;
use Core\Database\Model;

class Controller
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}