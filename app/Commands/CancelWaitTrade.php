<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CancelWaitTrade extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:CancelWaitTrade';
    protected $description = '黑鑽名單重置';
    //   /usr/bin/php /home/winner/spark app:CancelWaitTrade
    public function run(array $params)
    {
        $Year = date('Y'); //當年
        log_message('alert', $Year ."CancelTrade Start");
        $oLibA = new \App\Libraries\CancelTrade();
        $oLibA->cancelWait();
        log_message('alert', $Year ."CancelTrade End");
    }
}
