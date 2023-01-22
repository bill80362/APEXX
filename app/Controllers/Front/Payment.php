<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Payment extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oPayment = new \App\Models\Payment\Payment();
        $oPayment->where("Status","Y");
        $oPayment->orderBy("PaymentID","DESC");
        $List = $oPayment->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
