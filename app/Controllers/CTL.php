<?php

namespace App\Controllers;

class CTL extends BaseController
{
    public function cancelWaitTrade()
    {
        $oLibA = new \App\Libraries\CancelTrade();
        $oLibA->cancelWait();
        echo "cancelWaitTrade Finish";
    }
    public function mailWaitTrade()
    {
        $oLibA = new \App\Libraries\MailWaitTrade();
        $oLibA->mail();
        echo "mail Finish";
    }
}
