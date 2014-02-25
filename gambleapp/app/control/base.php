<?php

namespace control;

use ZPHP\Controller\IController,
    ZPHP\Core\Config as ZConfig,
    ZPHP\Conn\Factory as CFactory,
    common;

class base implements IController
{
    protected $server;
    protected $params = array();

    protected $fd;
    protected $uid;
    protected $connection = null; //redis connection object
    protected $cache = null;
    protected $rankCache = null;

    public $e_no = -1; //-1 sucess
    public $data = null; //return client's data

    public function setServer($server)
    {
        $this->server = $server;
        $this->params = isset($server->getParams()['p']['params']) ? $server->getParams()['p']['params'] : '';
        $this->uid = isset($server->getParams()['p']['uid']) ? $server->getParams()['p']['uid'] : '';
        $this->fd = $server->getFd();
    }

    public function getServer()
    {
        return $this->server;
    }

    public function _before()
    {
        $this->initUsedModel();
        //
        $this->connection = common\connection::getConnection();
        $this->cache = common\cache::getCache();
        $this->rankCache = common\cache::getRankCache();
        return true;
    }

    public function _after()
    {

    }

    public function getParams()
    {
        return $this->params;
    }

    public function checkLogin()
    {
        if (!$this->uid) {
            throw new common\error('错误的用户id');
        }
        $uid = common\connection::getConnection()->getUid($this->fd);
        if (empty($uid)) {
            throw new common\error('非法请求.');
        }
    }

    private function initUsedModel()
    {
        $pattern = "/(Model)$/si";
        $vars = get_class_vars(get_class($this));
        $models = common\Utils::preg_grep_keys($pattern, $vars);
        if (!empty($models)) {
            foreach ($models as $model) {
                $this->$model = common\loadClass::getModel($model);
            }
        }
        //add Service
        $servicePattern = "/(Service)$/si";
        $services = common\Utils::preg_grep_keys($servicePattern, $vars);
        if (!empty($services)) {
            foreach ($services as $service) {
                $this->$service = common\loadClass::getService($service);
            }
        }
    }

}
