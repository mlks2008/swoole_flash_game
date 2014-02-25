<?php
use ZPHP\ZPHP;
$rootPath = dirname(__DIR__);
require dirname($rootPath) . '/ZPHP/ZPHP.php';
ZPHP::run($rootPath);