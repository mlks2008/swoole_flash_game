<?php

namespace socket;

use ZPHP\Socket\ICallback;
use ZPHP\Core\Config as ZConfig;
use ZPHP\Protocol;
use ZPHP\Core;
use ZPHP\Common\Debug;
use common;

class Swoole implements ICallback
{


    public function onStart()
    {
        swoole_set_process_name("ansen: master process"); //master进程名称
        $port = ZConfig::getField('socket', 'port');
        Debug::debug("server start port[{$port}] version " . SWOOLE_VERSION . "... \n");
        $timerConfigs = ZConfig::getField('socket', 'times');
        $params = func_get_args();
//        var_dump($timerConfigs);
        if (!empty($timerConfigs)) {
            foreach ($timerConfigs as $time) {
                \swoole_server_addtimer($params[0], $time);
            }
        }

        //game start
        common\connection::setServer($params[0]);
        common\game::setRuncount();
        $count = common\game::getRuncount();
        Debug::log("begin game round[{$count}]\n");

        //when server restart
        $nowTime = time();
        $nextRunTime = common\game::getNextRunTime();
        if ($nowTime >= $nextRunTime) {
            $newNextRunTime = $nowTime + ZConfig::getField('init', 'firstRunTime');
            //set new next round
            common\game::setNextRunTime($newNextRunTime);
        }
        //:~end
    }

    public function onConnect()
    {
        $params = func_get_args();
        $serv = $params[0];
        $fd = $params[1];

        $clientInfo = common\connection::connectionInfo($fd);
//        print_r($clientInfo);
        Debug::debug("Client[ip:{$clientInfo['remote_ip']}:{$clientInfo['remote_port']}, fd:{$fd}] Connect" . PHP_EOL);
        common\connection::getConnection()->addFd($fd);

        $result = array(
            'e_no' => -1,
            'e_msg' => '',
            'api' => 'client\\connect',
            'data' => array(),
        );
        $_d = $this->getDataEof(json_encode($result));
        $this->sendOne($serv, $fd, $_d);
    }

    public function onReceive()
    {
        $beginTime = microtime(true);

        $params = func_get_args();
        $data = trim($params[3]);
        $serv = $params[0];
        $fd = $params[1];
        Debug::debug("get data: {$data} from $fd\n");
        if (empty($data)) {
            return;
        }

        //todo remove. for flash
        if ('<policy' == substr($data, 0, 7)) {
            \swoole_server_send($serv, $fd, "<cross-domain-policy>
                    <allow-access-from domain='*' to-ports='*' />
                    </cross-domain-policy>\0");
            swoole_server_close($serv, $fd);
        }
        //:~end

        //check dataeof
        $data = $this->parsedataEof($data);

        $server = Protocol\Factory::getInstance(Core\Config::getField('socket', 'protocol'));
        $result = $server->parse($data);
        if (empty($result['a'])) {
            $result = array(
                'e_no' => 9999999,
                'e_msg' => 'wrong client data',
                'data' => $data,
            );
            $d = \json_encode($result);
            $d = $this->getDataEof($d);
            \swoole_server_send($serv, $fd, $d);
        } else {
            $server->setFd($fd);
            $server = $this->route($server);
            $returnData = $server->getData();
            $endTime = (microtime(true) - $beginTime) * 1000;
            $returnData['_elapsed'] = $endTime;
            $d = \json_encode($returnData);
            $d = $this->getDataEof($d);
            \swoole_server_send($serv, $fd, $d);
            Debug::info("server return data:{$d}\n");
            Debug::info("[server time used:{$endTime} ms.]\n");
        }
    }

    private function sendOne($serv, $fd, $data)
    {
        \swoole_server_send($serv, $fd, $data);
    }

    private function sendAll($serv, $data, $channel = 'ALL')
    {
        $list = common\connection::getConnection()->getChannel($channel);
        if (empty($list)) {
            return;
        }
        foreach ($list as $fd) {
            $this->sendOne($serv, $fd, $data);
        }
    }

    function onTask($serv, $task_id, $from_id, $data)
    {
        if (empty($data)) {
            return;
        }

        $sendStr = $this->getDataEof($data);

        $parseD = json_decode($data, true);
        //send one
        if (isset($parseD['cmd']) && isset($parseD['_fd'])) {
            $this->sendOne($serv, $parseD['_fd'], $sendStr);
            //$serv->finish("OK:send client.");
        }
        //broadcast
        if (isset($parseD['broadcast']) && $parseD['broadcast'] === true) {
            $this->sendAll($serv, $sendStr, $parseD['channel']);
        }
        Debug::info("task send data:{$sendStr}\n");

    }

    function onFinish($serv, $data)
    {
        Debug::info("AsyncTask Finish:Connect.PID=" . posix_getpid() . PHP_EOL);
    }

    public function onClose()
    {
        $params = func_get_args();
        $fd = $params[1];

        $uid = common\connection::getConnection()->getUid($fd);
        common\connection::getConnection()->delete($fd, $uid);
//        common\connection::close($fd);
        Debug::debug("Client {$fd}: closed\n");
    }

    public function onShutdown()
    {
        Debug::debug("Shutdown server over...\n");
        common\connection::getConnection()->clear();
    }

    public function onTimer()
    {
        $params = func_get_args();
        //$serv = $paramet_s[0];
        $interval = $params[1]; //ms
        switch ($interval) {
            case 1000: //rungame
                $count = common\game::getRuncount();
                Debug::log("check execute game round[{$count}]\n");

                $nextRunTime = common\game::getNextRunTime();
                if ($nextRunTime == time()) {
                    //get pre round data
                    common\game::run();
                    $count = common\game::getRuncount();
                    Debug::log("calculate round[{$count}] data to client over.\n");
                    common\game::setRuncount();
                    $count = common\game::getRuncount();
                    Debug::log("begin game round[{$count}]\n");
                }
                break;
            case 10000:
                $list = common\connection::online();
                $onlineUsers = count($list);
                Debug::debug("{$interval} ms online users[{$onlineUsers}].\n");
                print_r($list);
                break;
        }
    }

    public function onWorkerStart()
    {
        swoole_set_process_name("ansen: work process"); //worker进程名称

        $params = func_get_args();
        $serv = $params[0];
        $worker_id = $params[1];

        //new version support
//        if($worker_id >= $serv->setting['worker_num']) {
//            swoole_set_process_name("ansen: task process");    //task name
//        } else {
//            swoole_set_process_name("ansen: work process"); //worker name
//        }
        common\connection::setServer($serv); //把serv加入到connection中
        Debug::debug("WorkerStart[$worker_id]|pid=" . posix_getpid() . ".\n");
    }

    public function onWorkerStop()
    {
        $params = func_get_args();
        $worker_id = $params[1];
        Debug::debug("WorkerStop[$worker_id]|pid=" . posix_getpid() . ".\n");
    }


    private function parsedataEof($data)
    {
        $dataEof = ZConfig::getField('socket', 'data_eof');
        if (($pos = strpos($data, $dataEof)) !== FALSE) {
            $data = substr($data, 0, $pos);
            Debug::log("get data[parse eof]: {$data}\n");
        }
        return $data;
    }

    private function getDataEof($data)
    {
        $dataEof = ZConfig::getField('socket', 'data_eof');
        return $data . $dataEof;
    }


    private function route($server)
    {
        try {
            common\route::route($server);
        } catch (common\error $e) {
            //错误返回
            $result = array(
                'e_no' => $e->e_no,
                'e_msg' => $e->e_msg,
                'api' => $server->getCtrl(),
                'data' => $e->e_data,
            );
            $server->display($result);
        }
        return $server;
    }

}