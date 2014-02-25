<?php
namespace control\user;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;

use model\useranteModel;
use model\userModel;

class ante extends base
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
//                    'ante' => 'apple',
//                    'type' => 1   //1:1 2:100
//                ),
//            ),
//        );
        $this->checkLogin();

        //
        $nextRunTime = common\game::getNextRunTime();
        if (time() > $nextRunTime) {
            throw new common\error('非法押注.');
        }

        $anteName = $this->params['ante'];
        if (isset($this->params['type'])) {
            $anteType = intval($this->params['type']);
            if ($anteType == 1) {
                $anteRate = 1;
            } elseif ($anteType == 2) {
                $anteRate = 100;
            } else {
                $anteRate = ZConfig::getField('init', 'anteRate');
            }
        } else {
            $anteRate = ZConfig::getField('init', 'anteRate');
        }

        $currentGamecount = common\game::getRuncount();

        //check coin
        $userInfo = $this->userModel->getUserById($this->uid);
        $leftCoin = $userInfo['coin'] - $anteRate;
        if ($leftCoin < 0) {
            throw new common\error('押注不够.');
        }
//        $this->userModel->updUserById($this->uid, array('coin' => $leftCoin));


        $returnDate = 0;
        if (!$userAnte = $this->useranteModel->getAnteByUidGamecount($this->uid, $currentGamecount)) {
            $_d = array(
                'uid' => $this->uid,
                $anteName => $anteRate,
                'gameCount' => $currentGamecount,
                'created' => time()
            );
            $this->useranteModel->addAnte($_d);
            $returnDate = $anteRate;
        } else {
            $val = $userAnte[$anteName] + $anteRate;
            if ($val > 999) {
                throw new common\error('最大押注为999.');
            }
            //update
            $_d = array(
                $anteName => $val
            );
            $this->useranteModel->updAnteById($userAnte['id'], $_d, $userAnte);
            $returnDate = $_d[$anteName];
        }
        $this->userModel->updUserById($this->uid, array('coin' => $leftCoin));

        $this->data = array(
            $anteName => $returnDate,
            'coin' => $leftCoin
        );

        common\game::sendCurrentAnte();
    }
}