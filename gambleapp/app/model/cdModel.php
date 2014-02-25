<?php

namespace model;

use common;

class cdModel extends base
{
    protected $tableName = 'cd';

    protected $cache = null;
    private $cacheNS = "UCD"; //cache namespace

    public function __construct()
    {
        parent::__construct();
        $this->cache = common\cache::getCache();
    }

    private function getCacheKey($uid, $cdName)
    {
        return $this->cacheNS . ":uid_{$uid}:cd_{$cdName}";
    }

    //系统cd 获取用户持续登录天数
    public function getKeepLoginDays($uid, $cdName = 'keepLoginDays')
    {
        $timestamp = ceil(time() / 86400);
        if (!$cd = $this->getCd($uid, $cdName)) {
            $data = array(
                'cdCount' => 1,
                'cdTimeStamp' => $timestamp,
            );
            $this->addCd($uid, $cdName, $data);
            return 1;
        }
        //计算
        if ($cd['cdTimeStamp'] == $timestamp) {
            return $cd['cdCount'];
        }
        if ($timestamp - $cd['cdTimeStamp'] == 1) {
            //连续登录
            $data = array(
                'id' => $cd['id'],
                'cdCount' => $cd['cdCount'] + 1,
                'cdTimeStamp' => $timestamp,
            );
            $this->updCd($uid, $cdName, $data);
            return $data['cdCount'];
        } else {
            $data = array(
                'id' => $cd['id'],
                'cdCount' => 1,
                'cdTimeStamp' => $timestamp,
            );
            $this->updCd($uid, $cdName, $data);
            return 1;
        }

    }

    public function getCd($uid, $cdname)
    {
        $key = $this->getCacheKey($uid, $cdname);
        if (!$data = $this->cache->get($key)) {
            $data = $this->get(array('uid' => $uid, 'cdName' => $cdname));
            $data && $this->cache->set($key, json_encode($data));
            return $data;
        }
        return json_decode($data, true);
    }

    public function addCd($uid, $cdname, $data = array())
    {
        if (!$this->getCd($uid, $cdname)) {
            $data['uid'] = $uid;
            $data['cdName'] = $cdname;
            $data['id'] = $this->set($data);
            return $data;
        }
        return false;
    }

    public function updCd($uid, $cdname, $data)
    {
        if (empty($uid) || empty($cdname)) {
            throw new common\error('uid cdname must not empty.');
        }
        if ($ret = $this->set(array('uid' => $uid, 'cdName' => $cdname), $data)) {
            $key = $this->getCacheKey($uid, $cdname);
            $this->cache->delete($key);
        }
        return $ret;
    }

}