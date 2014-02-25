<?php
namespace control\user;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;

use model\useranteModel;
use model\userModel;

class antedice extends base
{

    /**
     * @var useranteModel;
     */
    public $useranteModel;
    /**
     * @var userModel;
     */
    public $userModel;

    public function main()
    {
//        $this->params = array(
//            'a' => "user\\ante",
//            'p' => array(
//                'uid' => 1,
//                'params' => array(
//                ),
//            ),
//        );
        $this->checkLogin();

        $currentGamecount = common\game::getRuncount() - 1; //获取前面一次押注
        if (!$userAnte = $this->useranteModel->getAnteByUidGamecount($this->uid, $currentGamecount)) {
            throw new common\error('还未押注不能比大小');
        }

        $this->useranteModel->updAnteDiceById($userAnte['id'], $userAnte);
        $anteDiceCount = !empty($userAnte['anteDice']) ? $userAnte['anteDice'] + 1 : 1;

        //deal rate
        if ($anteDiceCount == 1) {
            $winRate = 50;
        }
        if ($anteDiceCount == 2) {
            $winRate = 40;
        }
        if ($anteDiceCount == 3) {
            $winRate = 30;
        }
        if ($anteDiceCount == 4) {
            $winRate = 20;
        }
        if ($anteDiceCount == 5) {
            $winRate = 10;
        }

        srand((double)microtime() * 1000000);
        $randval = rand(0, 100);

        if ($randval > $winRate) {
            //lost
            $this->data['result'] = 'lose';
            $this->data['getScore'] = 0;
            $this->userModel->updUserCoinById($this->uid, array('coin' => -($userAnte['score'])));
            $packet = array(
                'inPacket' => $userAnte['score'],
                'outPacket' => 0
            );
        } else {
            //win
            $this->data['result'] = 'win';
            $this->data['getScore'] = $userAnte['score'] + $userAnte['score'] * ($anteDiceCount * 2 - 1);
            if ($anteDiceCount == 1) {
                $this->userModel->updUserCoinById($this->uid, array('coin' => $this->data['getScore'] - $userAnte['score']));
                $packet = array(
                    'inPacket' => 0,
                    'outPacket' => -abs($this->data['getScore'] - $userAnte['score'])
                );
            } else {
                $this->userModel->updUserCoinById($this->uid, array('coin' => $this->data['getScore']));
                $packet = array(
                    'inPacket' => 0,
                    'outPacket' => -abs($this->data['getScore'])
                );
            }
        }
        common\game::setInpacket($packet);
        $this->data['coin'] = $this->userModel->getUserById($this->uid)['coin'];
    }
}