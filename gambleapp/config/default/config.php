<?php
use ZPHP\ZPHP;

define('START_SERV_TIME', time());
$config = array(
    'server_mode' => 'Socket',
    'project_name' => 'gambleapp',
    'app_path' => 'app',
    'ctrl_path' => 'control',
    'project' => array(
        'language' => 'zh-cn',
        'debug_mode' => 1,
        'log_path' => 'log',
    ),
    'socket' => array(
        'host' => '0.0.0.0', //socket 监听ip
        'port' => 8991, //socket 监听端口
        'adapter' => 'Swoole', //socket 驱动模块
        'daemonize' => 0, //是否开启守护进程
        'times' => array(
            //'userdefine' => 8000, //用户定义
            'rungame' => 1000,
            'online' => 10000, //在线用户列表
        ), //定时服务
        'work_mode' => 3,
//        'worker_num' => 2,
        'worker_num' => 4,
        'client_class' => 'socket\\Swoole', //socket 回调类
        'protocol' => 'Json', //socket通信数据协议
        'call_mode' => 'ROUTE', //业务处理模式
//        'max_request' => 1,
        'max_request' => 10000,
        'heartbeat_idle_time' => 21600, //客户端最大闲置事件 去掉则disable 心跳
        'heartbeat_check_interval' => 30, //心跳监测时间 去掉则disable 心跳
        'log_file' => dirname(dirname(__DIR__)) . '/log/swoole.log',
        'dispatch_mode' => 2,
        'data_eof' => "~~code~~", //前后端统一处理
    ),
);
$publicConfig = array('pdo.php', 'connection.php', 'cache.php');
foreach ($publicConfig as $file) {
    $file = ZPHP::getRootPath() . DS . 'config' . DS . 'public' . DS . $file;
    $config += include "{$file}";
}

//load exception language
if (isset($config['project']['language'])) {
    $langFile = ZPHP::getRootPath() . DS . "{$config['app_path']}" . DS . "language" . DS . "{$config['project']['language']}" . DS . "ui.php";
    $config += include "{$langFile}";
}

//load game config
$gameConfig = array('init.php', 'item.php', 'map.php', 'reward.php', 'itemrate.php');
foreach ($gameConfig as $file) {
    $file = ZPHP::getRootPath() . DS . 'config' . DS . 'game' . DS . $file;
    $config += include "{$file}";
}
return $config;