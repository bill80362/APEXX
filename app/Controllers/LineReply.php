<?php

namespace App\Controllers;

use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class LineReply extends BaseController
{
    use ResponseTrait;
    public function reply(){
        //Log
        $oECReply = new \App\Models\ECReply\ECReply();
        $oECReply->protect(false);
        $oECReply->insert([
            "PostData"=>print_r($this->request->getGet("transactionId"),true),
        ]);
        //
        $transactionId = $this->request->getGet("transactionId");
        //
        $oLibLinePay = new \App\Libraries\Payment\LinePay();
        $rs = $oLibLinePay->returnSuccess($transactionId);
        //
        if(!$rs) return $this->respond(ResponseData::fail($oLibLinePay->ErrorMessage));
        else return $this->respond(ResponseData::success($oLibLinePay->TradeData));
    }
}
