<?php

namespace Phencil;

use \League\Plates\Engine;

class View
{
    private $_options;

    public function __construct($options)
    {
        $this->_options = $options;
    }

    public function render($template, $data)
    {
        //---------------------------------------------------------------------------
        // Init renderer

        $templatePath = $this->_options['templates'];
        $renderer = new Engine($templatePath);
        if (file_exists($templatePath . '/shared')) {
            $renderer->addFolder('shared', $templatePath . '/shared');
        }

        return $renderer->render($template, $data);
    }

}
