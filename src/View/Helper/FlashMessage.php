<?php

namespace Phencil\View\Helper;

use Phencil\View\Helper;

class FlashMessage extends Helper
{

    public function invoke($level = null, $message = null)
    {
        if (!$level && !$message) {
            // render messages
            $html = '';
            if (!empty($_SESSION['flash_messages'])) {
                foreach ($_SESSION['flash_messages'] as $data) {
                    $html .= <<<EOS
<div class="alert alert-{$data['level']} alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  {$data['message']}
</div>
EOS;
                }
                unset($_SESSION['flash_messages']);
            }
            return $html;

        } else {
            // add message
            if (!array_key_exists('flash_messages', $_SESSION)) $_SESSION['flash_messages'] = [];
            $_SESSION['flash_messages'][] = [
                'level'   => $level,
                'message' => $message
            ];
        }
    }

}
