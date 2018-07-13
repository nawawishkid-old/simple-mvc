<?php

function z() {
    echo 'zzz';
    return false;
}
function x() {
    $a = [];

    return empty($a) || z();
}

var_dump(x());