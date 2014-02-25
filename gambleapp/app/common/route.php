<?php
namespace common;
use ZPHP\Controller\IController,
    ZPHP\Core\Factory,
    ZPHP\Core\Config,
    ZPHP\ZPHP;

class route
{
    public static function route($server)
    {
        $ctrl = $server->getCtrl();
        $action = Config::get('ctrl_path', 'ctrl') . '\\' . $ctrl;
        $class = Factory::getInstance($action);
        if (!($class instanceof IController)) {
            throw new \Exception("ctrl error");
        }
        $class->setServer($server);
        $before = $class->_before();
        $exception = null;
        if ($before) {
            try {
                $method = $server->getMethod();
                if (\method_exists($class, $method)) {
                    $class->$method();
                } else {
                    throw new \Exception("no method {$method}");
                }
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        $class->_after();
        if ($exception !== null) {
            $exception->e_no = ($exception->getCode()) < 0 ? 0 : $exception->getCode();
            $exception->e_msg = $exception->getMessage();
            $exception->e_data = $class->data;
            throw $exception;
        }
        //正确返回
        $result = array(
            'e_no' => $class->e_no,
            'e_msg' => '',
            'api'=>  $ctrl, //指定哪个接口返回
            'data' => $class->data,
        );
        return $server->display($result);
    }
}