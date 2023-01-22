<?php

namespace App\Controllers\Admin\Payment;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Payment extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //
        $oPayment = new \App\Models\Payment\Payment();
        $oPayment->orderBy("PaymentID","DESC");
        $List = $oPayment->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $Status = $this->request->getVar("Status");
        $ChargePercent = $this->request->getVar("ChargePercent");
        $ChargeFee = $this->request->getVar("ChargeFee");
        $DeliveryFrozen = $this->request->getVar("DeliveryFrozen");
        //
        $oPayment = new \App\Models\Payment\Payment();
        //檢查ID
        $Data = $oPayment->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oPayment->protect(false);
        $updateData = [
            "Status"=>$Status,
            "ChargePercent"=>$ChargePercent,
            "ChargeFee"=>$ChargeFee,
            "DeliveryFrozen"=>$DeliveryFrozen,
        ];
        $oPayment->update($ID,$updateData);
        if($oPayment->errors()){
            $ErrorMsg = implode(",",$oPayment->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oPayment->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
}
