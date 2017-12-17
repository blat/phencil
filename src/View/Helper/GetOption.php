<?php

namespace Phencil\View\Helper;

use Phencil\View\Helper;
use \Phencil\App;

class GetOption extends Helper
{

    public function invoke($name)
    {
        return App::getOption($name);
    }

}
