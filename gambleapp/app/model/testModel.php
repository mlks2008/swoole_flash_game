<?php

namespace model;


class testModel extends base {

    protected $tableName = 'user';

    public function getAll(){
        return $this->gets();
    }


}