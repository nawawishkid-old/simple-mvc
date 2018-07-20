<?php

namespace App\Controller;

use Core\Controller;
use App\Model\PostMeta as Model;
use Core\Database\Connection;
use Core\Database\Controller as DatabaseController;
use Core\View\View;

class PostMeta extends Controller
{
    public function index($request, $response)
    {
        $conn = new Connection();
        $ctrl = new DatabaseController($conn);

        $ctrl->table('wp_postmeta')
             ->select('meta_id', 'meta_value')
             ->where('meta_id', '>', 300)
             ->andWhere('meta_id', '>', 600);

        $rows = $ctrl->fetch();

        return View::get('index', $rows);
    }

    public function form($request, $response)
    {
        return View::get('form');
    }

    public function upload($request, $response)
    {
        return View::get('upload', $request);
    }
}