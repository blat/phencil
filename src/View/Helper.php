<?php

namespace Phencil\View;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

abstract class Helper implements ExtensionInterface
{

    public function register(Engine $engine)
    {
        $function = self::getFunctionName($this);
        $engine->registerFunction($function, [$this, 'invoke']);
    }

    public static function getFunctionName($class)
    {
        return lcfirst((new \ReflectionClass($class))->getShortName());
    }

}
