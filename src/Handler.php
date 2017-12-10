<?php

namespace Phencil;

use \League\Plates\Engine;

class Handler
{
    private $_app;

    public function __construct($app)
    {
        $this->_app = $app;
    }

    public function getParam($name)
    {
        return $this->_app->getParam($name);
    }

    public function getFile($name)
    {
        return $this->_app->getFile($name);
    }

    public function render($template, $data)
    {
        //---------------------------------------------------------------------------
        // Init renderer

        $templatePath = $this->_app->getOption('templates');
        $renderer = new Engine($templatePath);
        if (file_exists($templatePath . '/shared')) {
            $renderer->addFolder('shared', $templatePath . '/shared');
        }

        return $renderer->render($template, $data);
    }

    public function redirect($url)
    {
        $this->_app->redirect($url);
    }

    public function sendFile($file, $filename = null)
    {
        $this->_app->sendFile($file, $filename);
    }

}
