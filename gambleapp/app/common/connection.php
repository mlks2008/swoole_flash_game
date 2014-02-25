<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 13-12-6
 * Time: 下午3:26
 */

namespace common;

use ZPHP\Core\Config as ZConfig,
    ZPHP\Conn\Factory as CFactory;
use ZPHP\Common\Debug;

class connection
{

    private static $connection;
    private static $serv;

    public static function getConnection()
    {
        if (empty(self::$connection)) {
            $config = ZConfig::get('connection');
            self::$connection = CFactory::getInstance($config['adapter'], $config);
        }
        return self::$connection;
    }

    public static function setServer($serv)
    {
        self::$serv = $serv;
    }

    public static function sendOne_bak($fd, $cmd, $data)
    {
        if (empty(self::$serv) || empty($fd) || empty($cmd)) {
            return;
        }
        $result = array(
            'e_no' => -1,
            'e_msg' => '',
            'cmd' => $cmd,
            'data' => $data,
            '_fd' => $fd
        );
        //$data = json_encode(array($cmd, $data));
        $data = json_encode($result);
        return \swoole_server_send(self::$serv, $fd, $data);
    }

    //use task
    public static function sendOne($fd, $cmd, $data)
    {
        if (empty(self::$serv) || empty($fd) || empty($cmd)) {
            return;
        }
        $result = array(
            'e_no' => -1,
            'e_msg' => '',
            'cmd' => $cmd,
            'data' => $data,
            '_fd' => $fd
        );
        $rs = json_encode($result);
        self::$serv->task($rs);
        Debug::info("send data:{$rs}\ncmd[{$cmd}] To fd[{$fd}] over with task process.\n");
    }

    public static function sendToChannel_bak($cmd, $data, $channel = 'ALL')
    {
        $list = self::getConnection()->getChannel($channel);
        if (empty($list)) {
            return;
        }
        foreach ($list as $fd) {
            self::sendOne($fd, $cmd, $data);
        }
    }

    //user task
    public static function sendToChannel($cmd, $data, $channel = 'ALL')
    {
        if (empty(self::$serv)) {
            return;
        }
        $sendData = array(
            'e_no' => -1,
            'e_msg' => '',
            'cmd' => $cmd,
            'data' => $data,
            'broadcast' => true,
            'channel' => $channel
        );
        $rs = json_encode($sendData);
        self::$serv->task($rs);
        Debug::info("send data:{$rs}\ncmd[{$cmd}] To channel[{$channel}] over with task process.\n");
    }

    public static function connectionInfo($fd)
    {
        if (empty(self::$serv) || empty($fd)) {
            return;
        }
        return swoole_connection_info(self::$serv, $fd);
    }

    public static function online($channel = 'ALL')
    {
        return self::getConnection()->getChannel($channel);
    }

    public static function close($fd)
    {
        if (empty(self::$serv) || empty($fd)) {
            return;
        }
        swoole_server_close(self::$serv, $fd);
//        $uid = self::getConnection()->getUid($fd);
//        self::getConnection()->delete($fd, $uid);
        //self::sendToChannel($serv, self::LOGOUT, array($uid)); 广播xxx离开了游戏
    }

    public static function reloadServ()
    {
        if (empty(self::$serv)) {
            return;
        }
        //todo 注意这里应该清楚 数据cache而不是 connection cache
        //self::getConnection()->clear();
        swoole_server_reload(self::$serv);
    }

    public static function shutdownServ()
    {
        if (empty(self::$serv)) {
            return;
        }
        swoole_server_shutdown(self::$serv);
    }


}