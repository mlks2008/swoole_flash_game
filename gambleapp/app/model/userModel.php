<?php

namespace model;

use common;

class userModel extends base
{
    protected $tableName = 'user';

    protected $cache = null;
    private $cacheNS = "USER"; //cache namespace

    public function __construct()
    {
        parent::__construct();
        $this->cache = common\cache::getCache();
    }

    private function getCacheKey($uid)
    {
        return $this->cacheNS . ":uid_{$uid}";
    }


    public function addUser($data = array())
    {
        return $this->set($data);
    }

    public function getUserById($uid)
    {
        $key = $this->getCacheKey($uid);
        if (!$data = $this->cache->get($key)) {
            $data = $this->get('id', $uid);
            $data && $this->cache->set($key, json_encode($data));
            return $data;
        }
        return json_decode($data, true);
    }

    public function updUserById($uid, $data)
    {
        if ($ret = $this->set('id', $uid, $data)) {
            $key = $this->getCacheKey($uid);
            $this->cache->delete($key);
        }
        return $ret;
    }

    public function updUserCoinById($uid, $data)
    {
        if ($ret = $this->change('id', $uid, $data)) {
            $key = $this->getCacheKey($uid);
            $this->cache->delete($key);
        }
        return $ret;
    }

}