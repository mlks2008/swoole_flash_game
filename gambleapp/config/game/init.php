<?php
//初始化和游戏配置
return array(
    'init' => array(
        'user' => array(
            'coin' => 10000, //用户初始化钱币
        ),
        'anteRate' => 10, //单次押注数量
        'firstRunTime' => 20, //第一次游戏结算运行时间
        'flashGetReustlShowTime' => 5, //前端获取结果后跑动的时间
        'flashGetUpDownTime' => 5, //比大小时间
        'flashGetUpDownShowTime' => 0, //比大小结束后显示时间
        'flashAnteTime' => 20, //押注时间

        'packet' => 200, //每一轮服务器吃币阶段总钱包基数
        'packetRate' => 0.3 ////每一轮服务器吐币阶段比率packet * packetRate
    ),
);