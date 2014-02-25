<?php
return array(
    'cache'=>array(
        'net' => array( //网络cache配置
            'adapter' => 'Redis',
            'name' => 'nc',
            'pconnect' => true,
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 5,
        ),
    ),
);
