<?php
namespace common;

use ZPHP\Core\Config;

class error extends \Exception
{

    function __construct($message = '', $code = 0)
    {
        $msg = Config::get($message);
        $msg = empty($msg) ? $message : $msg;
        parent::__construct($msg, $code);
    }

}