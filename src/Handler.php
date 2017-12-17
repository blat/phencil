<?php

namespace Phencil;

use \League\Plates\Engine;

class Handler
{
    private $_app;
    private $_helpers;

    public function __construct($app)
    {
        $this->_app = $app;

        $this->_helpers = [];
        foreach (glob(__DIR__ . '/View/Helper/*.php') as $helperFile) {
            require_once $helperFile;
        }
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, View\Helper::class)) {
                $function = View\Helper::getFunctionName($class);
                $this->_helpers[$function] = $class;
            }
        }
    }

    public function getParam($name)
    {
        return $this->_app->getParam($name);
    }

    public function getFile($name)
    {
        return $this->_app->getFile($name);
    }

    public function render($template, $data = [])
    {
        //---------------------------------------------------------------------------
        // Init renderer

        $templatePath = $this->getOption('templates');
        $renderer = new Engine($templatePath);
        if (file_exists($templatePath . '/shared')) {
            $renderer->addFolder('shared', $templatePath . '/shared');
        }
        foreach ($this->_helpers as $class) {
            $renderer->loadExtension(new $class);
        }

        return $renderer->render($template, $data);
    }

    public function __call($helper, $args)
    {
        if (!isset($this->_helpers[$helper])) {
            throw new \Exception("Helper `$helper` doesn't exist");
        }

        $class = $this->_helpers[$helper];
        $helper = new $class();
        return call_user_func_array([$helper, 'invoke'], $args);
    }

    public function redirect($url)
    {
        $this->_app->redirect($url);
    }

    public function error($code, $message = '')
    {
        $this->_app->error($code, $message);
    }

    public function sendFile($file, $filename = null)
    {
        $this->_app->sendFile($file, $filename);
    }

}
