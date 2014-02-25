<?php

namespace model;

use common;

class gameModel extends base
{
    protected $tableName = 'game';

    public function __construct()
    {
        parent::__construct();
    }


    public function addGame($data)
    {
        return $this->set($data);
    }

    public function updGame($field, $value, $data)
    {
        return $this->set($field, $value, $data);
    }

    public function getGame()
    {
        return $this->gets();
    }
}