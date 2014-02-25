<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 13-12-6
 * Time: 下午3:26
 */

namespace common;

use common;

class Utils
{


    public static function getInteger(array $params, $key, $default = null, $abs = true, $notEmpty = false)
    {

        if (!isset($params[$key])) {
            if ($default !== null) {
                return $default;
            }
            throw new common\error("no params {$key}");
        }

        $integer = isset($params[$key]) ? \intval($params[$key]) : 0;

        if ($abs) {
            $integer = \abs($integer);
        }

        if ($notEmpty && empty($integer)) {
            throw new common\error('params no empty');
        }

        return $integer;
    }

    public static function getString($params, $key, $default = null, $notEmpty = false)
    {
        $params = (array)$params;

        if (!isset($params[$key])) {
            if (null !== $default) {
                return $default;
            }
            throw new common\error("no params {$key}");
        }

        $string = \trim($params[$key]);

        if (!empty($notEmpty) && empty($string)) {
            throw new common\error('params no empty');
        }

        return \addslashes($string);
    }

    public static function preg_grep_keys($pattern, $input, $flags = 0)
    {
        $keys = preg_grep($pattern, array_keys($input), $flags);
        $vals = array();
        foreach ($keys as $key) {
            $vals[] = $key;
        }
        return $vals;
    }

} 