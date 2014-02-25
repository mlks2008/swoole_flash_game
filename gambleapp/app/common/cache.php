<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 13-12-6
 * Time: 下午3:26
 */

namespace common;

use ZPHP\Core\Config as ZConfig,
    ZPHP\Cache\Factory as ZCache,
    ZPHP\Rank\Factory as ZRank;
class cache
{
    private static $cache;      //用户数据cache
    private static $rankCache;  //排行榜cache

    public static function getCache(){
        if (empty(self::$cache)) {
            $config = ZConfig::getField('cache', 'net');
            self::$cache = ZCache::getInstance($config['adapter'], $config);
        }
        return self::$cache;
    }

    public static function getRankCache(){
        if (empty(self::$rankCache)) {
            $config = ZConfig::getField('cache', 'net');
            self::$rankCache = ZRank::getInstance($config['adapter'], $config);
        }
        return self::$rankCache;
    }


}