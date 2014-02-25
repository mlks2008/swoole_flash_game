<?php
namespace control\server;
use common,
    control\base,
    ZPHP\Core\Config as ZConfig;

class shutdown extends base
{

    public function main()
    {
        echo "Shutdown server...\n";
        common\connection::sendToChannel(1, 'server shutdowning...');
        common\connection::shutdownServ();
    }

}
