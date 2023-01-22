<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
//        echo 123;
        $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
        $oPCHomePay->updateTrade(7);
    }
}
