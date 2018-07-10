<?php

namespace Core\Support;

class Debugger
{
    protected $preferences = [
        'varDump' => [
            'style' => [
                'backgroundColor' => '#ccc',
                'padding' => '10px'
            ]
        ]
    ];

    public function varDump($value)
    {
        $css = $this->preferences['varDump']['style'];
        $bg = $css['backgroundColor'];
        $padding = $css['padding'];

        echo "<pre style=\"background-color: $bg; padding: $padding;\">";
        \var_dump($value);
        echo '</pre>';
    }
}