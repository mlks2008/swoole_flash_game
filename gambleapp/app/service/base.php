<?php
namespace service;

use common;

class Base {

    public function __construct()
    {
        $this->initUsedModel();
        //todo get cache instance
    }

    protected function initUsedModel()
    {
        $pattern = "/(Model)$/si";
        $vars = get_class_vars(get_class($this));
        $models = common\Utils::preg_grep_keys($pattern, $vars);
        if (!empty($models)) {
            foreach ($models as $model) {
                $this->$model = common\loadClass::getModel($model);
            }
        }
    }

}