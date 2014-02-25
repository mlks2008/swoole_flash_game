<?php
namespace service;

use common;


use model\testModel;
class testService extends base
{

    /**
     * @var testModel;
     */
    public $testModel;


    public function test()
    {
        return $this->testModel->getAll();
    }


}