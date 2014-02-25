<?php

namespace model;

use ZPHP\Core\Config as ZConfig,
    ZPHP\Db\Pdo as ZPdo;

abstract class base
{
    private $_db = null;
    protected $tableName;

    public function __construct()
    {
        $config = ZConfig::get('pdo');
        $this->_db = new ZPdo($config, null, $config['dbname']);
        $this->_db->setTableName($this->tableName);
    }

    //1.get(array('uid'=>10000,'cdName'=>'test'),array('id','uid'));
    //2.get('uid',10000,array('id','uid'));
    protected function get($field, $value = null, array $rsfields = array())
    {
        if (is_array($field)) {
            if (is_array($value) && $value) {
                $fields = implode(',', $value);
            } else {
                $fields = '*';
            }
            $where = "1";
            foreach ($field as $fk => $fv) {
                $where .= " and {$fk}='" . addslashes($fv) . "'";
            }
            $rs = $this->_db->fetchEntity($where, null, $fields);
        } else {
            if (is_array($rsfields) && $rsfields) {
                $fields = implode(',', $rsfields);
            } else {
                $fields = '*';
            }
            $value = \addslashes($value);
            $rs = $this->_db->fetchEntity("{$field}='{$value}'", null, $fields);
        }
        return $rs;
    }

    protected function gets(array $items = array())
    {
        if (empty($items)) {
            return $this->_db->fetchAll();
        }
        $where = "1";
        foreach ($items as $k => $v) {
//            $where .= " and {$k}='{$v}'";
            $where .= " and {$k}='" . addslashes($v) . "'";

        }
        return $this->_db->fetchAll($where);
    }

    //单个记录操作方式 $this->set($data) ;$this->set(array('uid'=>10000,'cdName'=>'ansen'), array('cdCount'=>2)) ; $this->set('uid',20000000,$data) 2种方式
    protected function set($field, $value = null, array $row = array())
    {
        //add
        if (is_array($field) && $value === null && empty($row)) {
            $field = (object)$field;
            //todo changeTo _db->add
            return $this->_db->replace($field, \array_keys(\get_object_vars($field)));
        }
        //update
        if (is_array($field) && is_array($value)) {
            $where = "1";
            foreach ($field as $k => $v) {
                $where .= " and {$k}='" . addslashes($v) . "'";
            }
            $fields = array();
            $params = array();
            foreach ($value as $key => $val) {
                $fields[] = $key;
                $params[$key] = $val;
            }
            return $this->_db->update($fields, $params, $where);
        }

        //update
        if (is_string($field) && !empty($value) && is_array($row) && $row) {
            $fields = array();
            $params = array();
            foreach ($row as $key => $val) {
                $fields[] = $key;
                $params[$key] = $val;
            }
            $value = \addslashes($value);
            return $this->_db->update($fields, $params, "{$field}='{$value}'");
        }

    }

    //$this->change('id',1000,array('field' => 100))
    protected function change($field, $value = null, array $row = array())
    {
        $fields = array();
        $params = array();
        foreach ($row as $key => $val) {
            $fields[] = $key;
            $params[$key] = $val;
        }
        $value = \addslashes($value);
        return $this->_db->update($fields, $params, "{$field}='{$value}'", true);
    }

    protected function del($field, $value = null)
    {
        $value = \addslashes($value);
        return $this->_db->remove("{$field}='{$value}'");
    }

    //create uuid
    protected function getUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

}
