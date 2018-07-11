<?php

namespace App\Controller;

use Core\Controller;
use Core\Input\Request;
use Core\Output\Response;
use Core\Output\View;
use App\Model\Example;

class ExampleController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $data = [
            'name' => 'Nawawish',
            'data' => Example::all()
        ];
        
        View::render('home', $data);
    }

    public function store(Request $request, Response $response)
    {
        $data = [
            'name' => 'STORE!!'
        ];
        
        $response->data($data)->status(200)->emit();
    }
}