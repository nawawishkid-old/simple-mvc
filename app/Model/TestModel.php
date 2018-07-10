<?php

use Core\Database\Model;
use Core\Support\Debugger;

$model = new Model('wp_postmeta');
$model->add([
    'post_id' => 21,
    'meta_key' => 'สวัสดีครับ',
    'meta_value' => 'อาโล้ห่าาาา!!!     '
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
// var_dump($model->toJson());

$model->update([
    'meta_value' => 'ฮายยยยยย     '
])->where('post_id', '=', 21);
$model->save();

// $model->create();
$model->delete()
        ->where('post_id', '=', 21);
$model->confirmDelete();