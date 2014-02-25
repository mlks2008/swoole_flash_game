<?php
namespace control\user;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;

use model\userModel;

class login extends base
{

    /**
     * @var userModel;
     */
    public $userModel;

    public function main()
    {
//        $this->params = array(
//            'a' => "user\\login",
//            'p' => array(
//                'uid' => 1,
//                'params' => array(),
//            ),
//        );
        if (!$this->uid) {
            throw new common\error('错误的用户登陆');
        }

        $uInfo = $this->userModel->getUserById($this->uid);
        if (!$uInfo) {
            $initUserConfig = ZConfig::getField('init', 'user');
            $d = array(
                'id' => $this->uid,
                'coin' => $initUserConfig['coin'],
                'created' => time()
            );
            $this->userModel->addUser($d);
        }
        $uConnectInfo = $this->connection->get($this->uid);

        if (!$uConnectInfo) {
            $this->connection->add($this->uid, $this->fd);
            $this->connection->addFd($this->fd, $this->uid);
        } else {
            common\connection::close($uConnectInfo['fd']);
            $this->connection->add($this->uid, $this->fd);
            $this->connection->addFd($this->fd, $this->uid);
        }
//        common\connection::sendOne($this->fd,'login', 'test send one');
//        common\connection::sendToChannel('login', 'test send all');
        $this->data = array(
            'global' => array(
                'serverTime' => time(),
                'nextRoundTime' => common\game::getNextRunTime(),
                'currentRound' => common\game::getRuncount(),
            ),
            'positionList' => common\game::getPositionList(),
            'user' => $uInfo ? $uInfo : $d,
            'map' => ZConfig::get('map'),
            'item' => ZConfig::get('item')
        );
    }
}