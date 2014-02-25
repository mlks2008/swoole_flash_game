<?php
use ZPHP\ZPHP;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$rootPath = dirname(__DIR__);
require dirname($rootPath) . '/ZPHP/ZPHP.php';
ZPHP::setRootPath($rootPath);
//:~

$cmds = array('reload', 'shutdown', 'test');
$server = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
$cmd = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';
if (empty($server)) exit("wrong server.\n");
if (!in_array($cmd, $cmds)) exit("cmd must in [ " . implode("|", $cmds) . "]\n");
require_once "../config/{$server}/config.php";
$servInfo = array();
$servInfo['host'] = $config['socket']['host'];
$servInfo['port'] = $config['socket']['port'];
if (empty($servInfo['host']) || empty($servInfo['port'])) {
    exit("wrong server host or port.\n");
}
//:~


//发送给server的数据
$reqData = array(
    'a' => "server\\{$cmd}"
);
//$reqData = array(
//    'a' => "user\\login"
//);
//$reqData = array(
//    'a' => "user\\load"
//);


//调用方式: php optServ.php default reload
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); //异步非阻塞

$client->on("connect", function (swoole_client $cli) {
    global $reqData;
    echo "client connected\n";
    $data = json_encode($reqData);
    $cli->send($data);
});

$client->on("receive", function (swoole_client $cli, $data) {
    echo "Receive: $data\n";
});

$client->on("error", function (swoole_client $cli) {
    exit("error\n");
});

$client->on("close", function (swoole_client $cli) {
    echo "Connection close.\n";
});

$client->connect($servInfo['host'], $servInfo['port'], 0.5);

echo "connect to {$servInfo['host']}:{$servInfo['port']}\n";