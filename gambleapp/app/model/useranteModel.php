<?php

namespace model;

use common;

class useranteModel extends base
{
    protected $tableName = 'user_ante';

    protected $cache = null;
    private $cacheNS = "USERANTE"; //cache namespace

    public function __construct()
    {
        parent::__construct();
        $this->cache = common\cache::getCache();
    }

    private function getCacheKey($uid, $count)
    {
        return $this->cacheNS . ":uid_{$uid}:gamecount_{$count}";
    }

    public function addAnte($data = array())
    {
        return $this->set($data);
    }

    public function updAnteById($id, $data, $cacheDate)
    {
        if ($ret = $this->set('id', $id, $data)) {
            $key = $this->getCacheKey($cacheDate['uid'], $cacheDate['gameCount']);
            $this->cache->delete($key);
        }
        return $ret;
    }

    public function getAnteByUidGamecount($uid, $count)
    {
        $key = $this->getCacheKey($uid, $count);
        if (!$data = $this->cache->get($key)) {
            $data = $this->get(array('uid' => $uid, 'gameCount' => $count));
            $data && $this->cache->set($key, json_encode($data));
            return $data;
        }
        return json_decode($data, true);
    }

    public function getUserAnteListByRound($gameCount)
    {
        return $this->gets(array('gameCount' => $gameCount));
    }

    public function updAnteDiceById($id, $item)
    {
        if ($ret = $this->change('id', $id, array('anteDice' => 1))) {
            $key = $this->getCacheKey($item['uid'], $item['gameCount']);
            $this->cache->delete($key);
        }
        return $ret;
    }

}