<?php

$x = ['a' => 'abc', 'b' => 'def'];

array_walk($x, function (&$value, $key) {
    $value = "$key = $value";
});

var_dump($x);