<?php
//独立的游戏逻辑体
namespace common;

use common;
use ZPHP\Core\Config as ZConfig;
use ZPHP\Common\Debug;

class game
{

    private static $model;
    public static $keyNextRunTime = 'NEXTROUNDTIME';
    public static $keyCountRun = 'COUNTROUND';
    public static $CMDsendScore = 'CMDSCORE';
    public static $CMDsendReward = 'CMDREWARD';
    public static $CMDsendCurrentAnteList = 'CMDANTELIST';
    public static $CMDpositionList = 'CMDPOSITIONLIST';

    private static $gameTypes = array('dsy', 'dhc', 'dmg', 'xhc', 'mtx', 'xsy', 'xsx', 'null');
    private static $positionlist = array();

    //
    public static function run()
    {
        //get reward
        $reward = self::getReward();
        Debug::debug("get reward:\n");
        print_r($reward);

        //get next run time
        $reward['nextRunTime'] = self::setGameNextRunTime($reward['animationTime']);
        if (in_array($reward['name'], self::$gameTypes)) {

            if ($reward['name'] == 'dsy') {
                $reward['position'] = array(15, 19, 7);
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameDSY();
            }
            if ($reward['name'] == 'dhc') {
                $map = ZConfig::get('map');
                $point = rand(0, 23); //22
                $val1 = $point;
                $val2 = isset($map[$val1 + 1]) ? $val1 + 1 : 0;
                $val3 = isset($map[$val2 + 1]) ? $val2 + 1 : 0;
                $val4 = isset($map[$val3 + 1]) ? $val3 + 1 : 0;
                $val5 = isset($map[$val4 + 1]) ? $val4 + 1 : 0;
                $val6 = isset($map[$val5 + 1]) ? $val5 + 1 : 0;
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameDHC($val1, $val2, $val3, $val4, $val5, $val6);
            }
            if ($reward['name'] == 'xhc') {
                $map = ZConfig::get('map');
                $point = rand(0, 23);
                $val1 = isset($map[$point - 1]) ? $point - 1 : 23;
                $val2 = $point;
                $val3 = isset($map[$point + 1]) ? $point + 1 : 0;
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameXHC($val1, $val2, $val3);
            }
            if ($reward['name'] == 'dmg') {
                $randval1 = rand(0, 3);
                $randval2 = rand(4, 7);
                $randval3 = rand(8, 11);
                $randval4 = rand(12, 15);
                $randval5 = rand(16, 19);
                $randval6 = rand(20, 23);
                $reward['position'] = array($randval1, $randval2, $randval3, $randval4, $randval5, $randval6);
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameDMG($randval1, $randval2, $randval3, $randval4, $randval5, $randval6);
            }
            if ($reward['name'] == 'mtx') {
                $randval1 = rand(0, 6);
                $randval2 = rand(7, 15);
                $randval3 = rand(16, 23);
                $reward['position'] = array($randval1, $randval2, $randval3);
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameMTX($randval1, $randval2, $randval3);
            }
            if ($reward['name'] == 'xsy') {
                $reward['position'] = array(1, 6, 12);
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameXSY();
            }
            if ($reward['name'] == 'xsx') {
                //set reward position
                $reward['position'] = array(4, 10, 16, 22);
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameXSX();
            }
            if ($reward['name'] == 'null') {
                common\connection::sendToChannel(self::$CMDsendReward, $reward);
                self::gameNULL();
            }

        } else {
            //default
            //send reward to all
            common\connection::sendToChannel(self::$CMDsendReward, $reward);
            //结算
            self::getScoreTypeDefault($reward);
            //纪录当前的position 最多10 盘
            self::setPostionList($reward['position']);
        }
        //结算packet
        $Packet = self::getCurrentRoundInOutPacket();
        self::setInpacket($Packet);
    }

    private static function setPostionList($position)
    {
        array_push(self::$positionlist, $position);
        array_values(self::$positionlist);
        self::$positionlist = array_slice(self::$positionlist, -10);

        $gameModel = self::getModel('gameModel');
        $rs = $gameModel->getGame()[0];
        $_d = array(
            'positionList' => json_encode(self::$positionlist)
        );
        $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
        common\connection::sendToChannel(self::$CMDpositionList, self::$positionlist);
    }

    public static function getPositionList()
    {
        $gameModel = self::getModel('gameModel');
        $rs = $gameModel->getGame()[0];
        if ($rs['positionList']) {
            return json_decode($rs['positionList'], true);
        } else {
            return array();
        }
    }

    private static function getModel($name)
    {
        if (!isset(self::$model[$name])) {
            self::$model[$name] = common\loadClass::getModel($name);
        }
        return self::$model[$name];
    }

    private static function getRewardIndex($array = array(), $randomNum = 0)
    {
        if (empty($array) || empty($randomNum)) {
            return false;
        }

        if (in_array($randomNum, $array)) {
            return $randomNum;
        }

        array_push($array, $randomNum);
        asort($array);
        $oldD = array_values($array);
        $newD = array_flip($oldD);
        $index = $newD[$randomNum] + 1;
        return $oldD[$index];
    }

//    private static function make_seed()
//    {
//        list($usec, $sec) = explode(' ', microtime());
//        return (float)$sec + ((float)$usec * 100000);
//    }

    private static function getReward()
    {
//        mt_srand(self::make_seed());
        $type = self::getNowPacketType();
        Debug::error("typeIn\n");
        $itemRate = ZConfig::get('itemrate');

        if ($type == 'typeIn') {
            //吃币阶段
            $upCount = 5000;
            $randItemRateval = mt_rand(1, $upCount);
        } else {
            //吐币阶段
            $upCount = 1250;
            $randItemRateval = mt_rand(1, $upCount);
        }
        if ($randItemRateval > 1000) {
            $getItem = $itemRate[$upCount];
            $positions = $getItem['position'];
            $randval = mt_rand(0, count($positions) - 1);
            $randval = $positions[$randval];
        } else {
            $itemRateKey = array_keys($itemRate);
            $index = self::getRewardIndex($itemRateKey, $randItemRateval);
            $getItem = $itemRate[$index];
            $positions = $getItem['position'];
            if (count($positions) == 1) {
                $randval = $positions[0];
            } else {
                $randval = mt_rand(0, count($positions) - 1);
                $randval = $positions[$randval];
            }
        }

        //todo remove it
//        if ($randval == 9 || $randval == 21) {
//            $randval = 22;
//        }
//        $randval = 9;
        $mapD = ZConfig::get('map');
        $ret = $mapD[$randval];
        if (isset($ret['goodluck1'])) {
            $reward = ZConfig::getField('reward', 'goodluck1');
            $rewardKey = array_keys($reward);
            $randV = rand(1, 1000);
            $rewardIndex = self::getRewardIndex($rewardKey, $randV);
            $getReward = $reward[$rewardIndex];
            $getReward['from'] = array('name' => 'goodluck1', 'position' => $randval);
        } elseif (isset($ret['goodluck2'])) {
            $reward = ZConfig::getField('reward', 'goodluck2');
            $rewardKey = array_keys($reward);
            $randV = rand(1, 1000);
            $rewardIndex = self::getRewardIndex($rewardKey, $randV);
            $getReward = $reward[$rewardIndex];
            $getReward['from'] = array('name' => 'goodluck2', 'position' => $randval);
        } else {
            //default
            $rewardName = array_keys($ret)[0];
            if ($ret[$rewardName] == 'default') {
                $item = ZConfig::get('item');
                if (!$rate = $item[$rewardName]) {
                    throw new common\error('配置出错.');
                }
            } else {
                $rate = $ret[$rewardName];
            }
            $reward = ZConfig::getField('reward', 'default');
            $getReward = array(
                'name' => $rewardName,
                'rate' => $rate,
                'position' => $randval,
                'animationTime' => $reward['animationTime'],
            );
        }
        return $getReward;
    }

    private static function getAnteList()
    {
        $useranteModel = self::getModel('useranteModel');
        $currentRound = self::getRuncount();
        $list = $useranteModel->getUserAnteListByRound($currentRound);
        return $list;
    }

    private static function getScoreTypeDefault($reward = array())
    {
        $anteList = self::getAnteList();
        if (empty($anteList) || empty($reward)) {
            return;
        }
        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $rewardName = $reward['name'];
        $rate = $reward['rate'];

        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $sucess = false;
            $score = 0;
            if ($anteVal = $item[$rewardName]) {
                $sucess = true;
                $score = $anteVal * $rate;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }
            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }
    }

    private static function setGameNextRunTime($time = 0)
    {
        $retrunArray = array();
        //update next time
        //前端跑动时间＋flash特效时间＋比大小时间＋比大小结束后显示时间＋押注时间
        $nextRunTime = self::getNextRunTime();

        $retrunArray['flashGetReustlShowTime'] = ZConfig::getField('init', 'flashGetReustlShowTime');
        $retrunArray['flashShowTime'] = $time;
        $retrunArray['flashGetUpDownTime'] = ZConfig::getField('init', 'flashGetUpDownTime');
        $retrunArray['flashGetUpDownShowTime'] = ZConfig::getField('init', 'flashGetUpDownShowTime');
        $retrunArray['flashAnteTime'] = ZConfig::getField('init', 'flashAnteTime');
        $retrunArray['serverTime'] = time();
        $retrunArray['nextRoundTime'] = $nextRunTime + $retrunArray['flashGetReustlShowTime'] + $retrunArray['flashShowTime'] + $retrunArray['flashGetUpDownTime'] + $retrunArray['flashGetUpDownShowTime'] + $retrunArray['flashAnteTime'];
        self::setNextRunTime($retrunArray['nextRoundTime']);
        return $retrunArray;
    }

    //packet
    private static function getCurrentRoundInOutPacket()
    {
        $list = self::getAnteList();
        if (empty($list)) {
            return;
        }
        $returnArray = array();
        $inData = array('bar' => 0, 'seven' => 0, 'star' => 0, 'watermelon' => 0, 'ring' => 0, 'mango' => 0, 'orange' => 0, 'apple' => 0);
        $outData = 0;
        foreach ($list as $item) {
            $inData['bar'] += $item['bar'];
            $inData['seven'] += $item['seven'];
            $inData['star'] += $item['star'];
            $inData['watermelon'] += $item['watermelon'];
            $inData['ring'] += $item['ring'];
            $inData['mango'] += $item['mango'];
            $inData['orange'] += $item['orange'];
            $inData['apple'] += $item['apple'];
            $outData += $item['score'];
        }
        $returnArray['inPacket'] = array_sum($inData);
        $returnArray['outPacket'] = $outData;
        return $returnArray;
    }

    public static function setInpacket($val = array())
    {
        $gameModel = self::getModel('gameModel');
        $rs = $gameModel->getGame()[0];
        $thisRoundInPacket = $val['inPacket'];
        $thisRoundOutPacket = $val['outPacket'];

        $type = self::getNowPacketType();
        if ($type == 'typeIn') { //吃进
            $nowPacket = $rs['inPacket'] + $thisRoundInPacket;
            $outPacket = $rs['outPacket'] + $thisRoundOutPacket;
            $thisRoundNeedPacket = $rs['packetRound'] * ZConfig::getField('init', 'packet'); //200
            if ($nowPacket >= $thisRoundNeedPacket) { //升级
                if ($nowPacket % ZConfig::getField('init', 'packet') == 0) {
                    $getRound = $nowPacket / ZConfig::getField('init', 'packet');
                } else {
                    $getRound = ceil($nowPacket / ZConfig::getField('init', 'packet'));
                }
                $outPacket += $getRound * ZConfig::getField('init', 'packetRate');
                $nowPacket -= $getRound * ZConfig::getField('init', 'packetRate');
                $_d = array(
                    'inPacket' => $nowPacket,
                    'outPacket' => $outPacket,
                    'packetRound' => $getRound
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            } else {
                $_d = array(
                    'inPacket' => $nowPacket,
                    'outPacket' => $outPacket,
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            }
        } else { //吐出
            $nowPacket = $rs['inPacket'] + $thisRoundInPacket;
            $outPacket = $rs['outPacket'] - abs($thisRoundOutPacket);
            if ($outPacket < 0) {
                $outPacket = 0;
                $nowPacket = $nowPacket - abs($outPacket);
            }
            //检查是否需要升级
            $thisRoundNeedPacket = $rs['packetRound'] * ZConfig::getField('init', 'packet'); //200
            if ($nowPacket >= $thisRoundNeedPacket) { //升级
                if ($nowPacket % ZConfig::getField('init', 'packet') == 0) {
                    $getRound = $nowPacket / ZConfig::getField('init', 'packet');
                } else {
                    $getRound = ceil($nowPacket / ZConfig::getField('init', 'packet'));
                }
                $outPacket += $getRound * ZConfig::getField('init', 'packetRate');
                $nowPacket -= $getRound * ZConfig::getField('init', 'packetRate');;
                $_d = array(
                    'inPacket' => $nowPacket,
                    'outPacket' => $outPacket,
                    'packetRound' => $getRound
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            } else {
                $_d = array(
                    'inPacket' => $nowPacket,
                    'outPacket' => $outPacket,
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            }

        }
    }

    public static function setInpacket_bak($val = array())
    {
        $gameModel = self::getModel('gameModel');
        $rs = $gameModel->getGame()[0];
        $thisRoundInPacket = $val['inPacket'];
        $thisRoundOutPacket = $val['outPacket'];

        //
        $type = self::getNowPacketType();
        if ($type == 'typeIn') { //吃进
            $nowInpacket = $rs['inPacket'] + $thisRoundInPacket;
            if ($nowInpacket >= self::getInPacketUpLimit()) {
                $outPacket = self::getOutPacketDownLimit();
                $nowInpacket = $nowInpacket - $outPacket;
                $_d = array(
                    'inPacket' => $nowInpacket,
                    'outPacket' => $outPacket
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            } else {
                $_d = array(
                    'inPacket' => $nowInpacket,
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            }
        } else { //吐出
            $nowInpacket = $rs['inPacket'] + $thisRoundInPacket;
            $outPacket = $rs['outPacket'] - $thisRoundOutPacket;
            if ($outPacket <= 0) {
                $tmpD = $rs['outPacket'] - $thisRoundOutPacket;
                $nowInpacket = $nowInpacket + $tmpD;
                $outPacket = 0;

                $currentPacketRound = ceil($nowInpacket / ZConfig::getField('init', 'packet'));
                $currentPacketRound = $currentPacketRound <= $rs['packetRound'] ? $rs['packetRound'] : $currentPacketRound;

                $nowInpacket = $currentPacketRound * ZConfig::getField('init', 'packetRate');
                $_d = array(
                    'inPacket' => $nowInpacket,
                    'outPacket' => $outPacket,
                    'packetRound' => $currentPacketRound
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            } else {
                $_d = array(
                    'inPacket' => $nowInpacket,
                    'outPacket' => $outPacket,
                );
                $gameModel->updGame('packetRound', $rs['packetRound'], $_d);
            }

        }

    }

    private static function getOutPacketDownLimit($round = 0)
    {
        if ($round) {
            return self::getInPacketUpLimit($round) * ZConfig::getField('init', 'packetRate');
        } else {
            return self::getInPacketUpLimit() * ZConfig::getField('init', 'packetRate');
        }
    }

    private static function getInPacketUpLimit($round = 0)
    {
        if ($round) {
            $packetRound = $round;
        } else {
            $gameModel = self::getModel('gameModel');
            $packetRound = $gameModel->getGame()[0]['packetRound'];
        }
        $upLimit = $packetRound * ZConfig::getField('init', 'packet');
        return $upLimit;
    }

    private static function getOutPacketUpLimit()
    {
        return self::getInPacketUpLimit() * ZConfig::getField('init', 'packetRate');
    }

    private static function getNowPacketType()
    {
        $gameModel = self::getModel('gameModel');
        $rs = $gameModel->getGame()[0];
        if ($rs['outPacket'] > 0) {
            return 'typeOut';
        } else {
            return 'typeIn';
        }
    }


    // game 8 type
    private static function gameXSX()
    {
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }
        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $rewardName = 'apple';
        $rate = 4 * ZConfig::getField('item', $rewardName);
        $sucess = false;
        $score = 0;
        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            if ($anteVal = $item[$rewardName]) {
                $sucess = true;
                $score = $anteVal * $rate;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }
            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }
    }

    private static function gameNULL()
    {
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }
        $userModel = self::getModel('userModel');
        $sucess = false;
        $score = 0;
        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }
    }

    private static function gameDSY()
    {
        //77 双星 西瓜全中
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }
        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $rewardName1 = 'seven';
        $rewardRate1 = ZConfig::getField('item', $rewardName1);
        $rewardName2 = 'star';
        $rewardRate2 = ZConfig::getField('item', $rewardName2);
        $rewardName3 = 'watermelon';
        $rewardRate3 = ZConfig::getField('item', $rewardName3);

        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $score = 0;
            $sucess = false;
            if ($anteVal1 = $item[$rewardName1]) {
                $score += $anteVal1 * $rewardRate1;
            }
            if ($anteVal2 = $item[$rewardName2]) {
                $score += $anteVal2 * $rewardRate2;
            }
            if ($anteVal3 = $item[$rewardName3]) {
                $score += $anteVal3 * $rewardRate3;
            }
            if (!empty($score)) {
                $sucess = true;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }

            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }
    }

    private static function gameDHC($val1, $val2, $val3, $val4, $val5, $val6)
    {
        //进入Good Luck后发动，连续6个，若包括Good Luck算空
        self::gameDMG($val1, $val2, $val3, $val4, $val5, $val6);
    }

    private static function gameXHC($val1, $val2, $val3)
    {
        //进入Good Luck后发动，连续3个，若包括Good Luck算空
        self::gameMTX($val1, $val2, $val3);
    }

    private static function gameDMG($val1, $val2, $val3, $val4, $val5, $val6)
    {
        //进入Good Luck后发动，随机中6个，若包括Good Luck算空
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }

        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $map = ZConfig::get('map');
        $positions = array($val1, $val2, $val3, $val4, $val5, $val6);

        $result = array();
        foreach ($positions as $val) {
            $mapItem = $map[$val];
            $itemKey = array_keys($mapItem);
            if ($itemKey[0] != "goodluck1" && $itemKey[0] != "goodluck2") {
                $result[] = array(
                    'name' => $itemKey[0],
                    'rate' => $mapItem[$itemKey[0]] == 'default' ? ZConfig::getField('item', $itemKey[0]) : intval($mapItem[$itemKey[0]])
                );
            }
        }

        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $score = 0;
            $sucess = false;

            foreach ($result as $r) {
                if (($anteVal = $item[$r['name']])) {
                    $score += $anteVal * $r['rate'];
                }
            }
            if (!empty($score)) {
                $sucess = true;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }

            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }

    }

    private static function gameMTX($val1, $val2, $val3)
    {
        //进入Good Luck后发动，随机中3个，若包括Good Luck算空
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }

        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $map = ZConfig::get('map');
        $positions = array($val1, $val2, $val3);

        $result = array();
        foreach ($positions as $val) {
            $mapItem = $map[$val];
            $itemKey = array_keys($mapItem);
            if ($itemKey[0] != "goodluck1" && $itemKey[0] != "goodluck2") {
                $result[] = array(
                    'name' => $itemKey[0],
                    'rate' => $mapItem[$itemKey[0]] == 'default' ? ZConfig::getField('item', $itemKey[0]) : intval($mapItem[$itemKey[0]])
                );
            }
        }

        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $score = 0;
            $sucess = false;

            foreach ($result as $r) {
                if (($anteVal = $item[$r['name']])) {
                    $score += $anteVal * $r['rate'];
                }
            }
            if (!empty($score)) {
                $sucess = true;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }

            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }

    }

    private static function gameXSY()
    {
        //铜钟、芒果、橙子全中
        $anteList = self::getAnteList();
        if (empty($anteList)) {
            return;
        }
        $useranteModel = self::getModel('useranteModel');
        $userModel = self::getModel('userModel');

        $rewardName1 = 'ring';
        $rewardRate1 = ZConfig::getField('item', $rewardName1);
        $rewardName2 = 'mango';
        $rewardRate2 = ZConfig::getField('item', $rewardName2);
        $rewardName3 = 'orange';
        $rewardRate3 = ZConfig::getField('item', $rewardName3);

        foreach ($anteList as $item) {
            if (!$item['uid']) {
                continue;
            }
            $score = 0;
            $sucess = false;
            if ($anteVal1 = $item[$rewardName1]) {
                $score += $anteVal1 * $rewardRate1;
            }
            if ($anteVal2 = $item[$rewardName2]) {
                $score += $anteVal2 * $rewardRate2;
            }
            if ($anteVal3 = $item[$rewardName3]) {
                $score += $anteVal3 * $rewardRate3;
            }
            if (!empty($score)) {
                $sucess = true;
                $useranteModel->updAnteById($item['id'], array('score' => $score), $item);
                $userModel->updUserCoinById($item['uid'], array('coin' => $score));
            }

            $uInfo = $userModel->getUserById($item['uid']);
            //send
            $fd = common\connection::getConnection()->get($uInfo['id'])['fd'];
            common\connection::sendOne($fd, self::$CMDsendScore, array('coin' => $uInfo['coin'], 'sucess' => $sucess, 'getScore' => $score));
        }
    }


    public static function sendCurrentAnte()
    {
        $list = self::getAnteList();
        if (empty($list)) {
            return;
        }
        $sendData = array('bar' => 0, 'seven' => 0, 'star' => 0, 'watermelon' => 0, 'ring' => 0, 'mango' => 0, 'orange' => 0, 'apple' => 0);
        foreach ($list as $item) {
            $sendData['bar'] += $item['bar'];
            $sendData['seven'] += $item['seven'];
            $sendData['star'] += $item['star'];
            $sendData['watermelon'] += $item['watermelon'];
            $sendData['ring'] += $item['ring'];
            $sendData['mango'] += $item['mango'];
            $sendData['orange'] += $item['orange'];
            $sendData['apple'] += $item['apple'];
        }
        common\connection::sendToChannel(self::$CMDsendCurrentAnteList, $sendData);
    }

    //
    public static function setRuncount()
    {
        $gameModel = self::getModel('gameModel');
        $cacheObj = common\cache::getCache();

        $ret = self::getRuncount(); //current round
        if ($ret === false) {
            $_d = array(
                'currentCount' => 1,
                'nextRunTime' => time() + ZConfig::getField('init', 'firstRunTime'),
                'packetRound' => 1,
            );
            $gameModel->addGame($_d);
            //set count cache
            $cacheObj->set(self::$keyCountRun, $_d['currentCount']);
            //set next run time cache
            $cacheObj->set(self::$keyNextRunTime, $_d['nextRunTime']);
        } else {
            $_d = array(
                'currentCount' => $ret + 1
            );
            $gameModel->updGame('currentCount', $ret, $_d);
            //set count cache
            $cacheObj->set(self::$keyCountRun, $_d['currentCount']);
        }
    }

    public static function getRuncount()
    {
        $cacheObj = common\cache::getCache();
        if (!$ret = $cacheObj->get(self::$keyCountRun)) {
            $gameModel = self::getModel('gameModel');
            $rs = $gameModel->getGame();
            if (!$rs) { //no data
                $ret = false;
            } else {
                $ret = $rs[0]['currentCount'];
            }
        }
        return $ret;
    }

    public static function setNextRunTime($nextTime = 0)
    {
        $gameModel = self::getModel('gameModel');
        $ret = $gameModel->getGame();
        if (!$ret) {
            return;
        }
        $gameModel->updGame('currentCount', $ret[0]['currentCount'], array('nextRunTime' => $nextTime));
        $cacheObj = common\cache::getCache();
        $cacheObj->set(self::$keyNextRunTime, $nextTime);
    }

    public static function getNextRunTime()
    {
        $cacheObj = common\cache::getCache();
        if (!$ret = $cacheObj->get(self::$keyNextRunTime)) {
            $gameModel = self::getModel('gameModel');
            $ret = $gameModel->getGame()[0]['nextRunTime'];
        }
        return $ret;
    }
}