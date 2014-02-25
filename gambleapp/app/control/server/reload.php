<?php
namespace control\server;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;
use ZPHP\Common\Debug;

class reload extends base
{

    public function main()
    {
        Debug::info("Start Reload server...\n");
//        common\connection::sendToChannel(1, 'server reloading...');
        common\connection::reloadServ();
        common\connection::sendOne($this->fd, 'RELOADSERVER', 'server reload over.');
    }
}