<?php

use Core\Database\Model;

$model = new Model('wp_postmeta');
// $model->db->select(['column_a', 'column_b'], 'table_a')
//             ->where('column_a', '<', 555)
//             ->where('column_a', '<>', 333)
//             ->orWhere('column_b', '=', 'hahaha');
// $model->db->select(['*'], 'wp_posts')
//             ->where('ID', '>', 3);
// $model->find('meta_id', '>', 0)
//         ->where('meta_id', '<', 30);
$model->add([
    'post_id' => 20,
    'meta_key' => 'สวัสดีครับ',
    'meta_value' => 'อาโล้ห่าาาา!!!'
]);
$model->create();

// $model->create([
//     [
//         'post_id' => 20,
//         'meta_key' => 'nameeeee',
//         'meta_value' => 'Nawawish!!!'
//     ],
//     [
//         'post_id' => 40,
//         'meta_key' => 'surnameeeee',
//         'meta_value' => 'Samerpark!!!'
//     ]
// ]);
$model->select(['meta_id']);
var_dump($model->toJson());