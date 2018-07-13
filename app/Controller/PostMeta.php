<?php

namespace App\Controller;

use Core\Controller;
use App\Model\PostMeta as Model;
use Core\Database\Connection;
use Core\Database\Controller as DatabaseController;
use Core\Database\Query\Builder;

class PostMeta extends Controller
{
    public function index($request, $response)
    {
        // var_dump($response);
        $conn = new Connection();
        $ctrl = new DatabaseController($conn);
        $qb = new Builder();

        $ctrl->table('wp_postmeta')
             ->select('meta_id', 'meta_value')
             ->where('meta_id', '>', 300)
             ->andWhere('meta_id', '>', 600);

        var_dump($ctrl->get());
        $rows = $ctrl->fetch();

        echo '<pre>';
        var_dump($rows);
        echo '</pre>';
        // echo self::class . "::index()!";
    }
}