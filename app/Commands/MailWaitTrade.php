<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MailWaitTrade extends BaseCommand
{
	protected $group       = 'demo';
	protected $name        = 'app:MailWaitTrade';
	protected $description = '黑鑽名單重置';
    //   /usr/bin/php /home/winner/spark app:MailWaitTrade
	public function run(array $params)
	{
        $Year = date('Y'); //當年
        log_message('alert', $Year ."MailWaitTrade Start");
        $oLibA = new \App\Libraries\MailWaitTrade();
        $oLibA->mail();
        log_message('alert', $Year ."MailWaitTrade End");
	}
}