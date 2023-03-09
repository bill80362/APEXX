<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        echo 12345;
//        $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
//        $oPCHomePay->updateTrade(7);

        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $rs = $oMail->send("bill80362@gmail.com", "寄信測試", "寄信測試",true);
        var_dump($rs);
    }
}
