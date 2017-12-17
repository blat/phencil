<?php

namespace Phencil\View\Helper;

use Phencil\View\Helper;

class Asset extends Helper
{

    public function invoke($asset)
    {
        return $asset . '?v=' . filemtime('./' . $asset);
    }

}
