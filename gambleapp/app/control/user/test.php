<?php
namespace control\user;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;

use model\userModel;

class test extends base
{

    /**
     * @var userModel;
     */
    public $userModel;

    public function main()
    {
        //客户端传入数据 todo $this->params需要过滤一层
//        $this->params = array(
//            'a' => "user\\login",
//            'p' => array(
//                'uid' => 1,
//                'sign' => 'eyJ1aWQiOiIxIiwic2lnbnNlY3JldGtleSI6ImFuc2VuZ2FtZWZyYW1ld29yazEyMyFAIyJ9',
//                'extra' => array(),
//                'params' => array(
//                    'name' => 'ansen',
//                    'password' => 'ansen123',
//                ),
//            ),
//        );

        $this->data = $this->params;
        return $this->data;
    }


}
