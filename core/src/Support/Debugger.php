<?php

namespace Core\Support;

class Debugger
{
    protected $preferences = [
        'varDump' => [
            'style' => [
                'pre' => [
                    'backgroundColor' => '#ccc',
                    'padding' => '10px'
                ]
            ]
        ]
    ];

    public function varDump($value, string $title = null)
    {
        $args = [];
        $args['title'] = $title;

        $this->varDumpTemplate($value, $args);

        return $this;
    }

    private function varDumpTemplate($value, array $args = null)
    {
        if (! \is_null($args)) {
            $style = empty($args['style']) ? $this->preferences['varDump']['style'] : $args['style'];
        } else {
            $style = $this->preferences['varDump']['style'];
        }

        $title = isset($args['title']) 
                    ? "<h3>{$args['title']}</h3>"
                    : '';
        $preStyle = $style['pre'];
        
        \ob_start();
        \var_dump($value);
        $result = \ob_get_clean();

        echo <<<HTML
<div style="">
    <h3>$title</h3>
    <pre style="background-color: {$preStyle['backgroundColor']}; padding: {$preStyle['padding']};">
        $result
    </pre>
    <hr>
</div>
HTML;
    }
}