<?php

namespace App\Controllers\Admin\Shipping;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Shipping extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $oShipping = new \App\Models\Shipping\Shipping();
        $oShipping->orderBy("ShippingID", "DESC");
        $List = $oShipping->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $ShippingType = $this->request->getVar("ShippingType");
        $Status = $this->request->getVar("Status");
        $StatusOutlying = $this->request->getVar("StatusOutlying");
        $ShippingFee = $this->request->getVar("ShippingFee");
        $ShippingFeeOutlying = $this->request->getVar("ShippingFeeOutlying");
        $DeliverVolumeMax = $this->request->getVar("DeliverVolumeMax");
        $DeliverWeightMax = $this->request->getVar("DeliverWeightMax");
        $DeliveryFrozen = $this->request->getVar("DeliveryFrozen");
        $QueryLink = $this->request->getVar("QueryLink");
        //
        $oShipping = new \App\Models\Shipping\Shipping();
        //檢查ID
        $Data = $oShipping->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oShipping->protect(false);
        $updateData = [
            "Title"=>$Title,
            "ShippingType"=>$ShippingType,
            "Status"=>$Status,
            "StatusOutlying"=>$StatusOutlying,
            "ShippingFee"=>$ShippingFee,
            "ShippingFeeOutlying"=>$ShippingFeeOutlying,
            "DeliverVolumeMax"=>$DeliverVolumeMax,
            "DeliverWeightMax"=>$DeliverWeightMax,
            "DeliveryFrozen"=>$DeliveryFrozen,
            "QueryLink"=>$QueryLink,
        ];
        $oShipping->update($ID, $updateData);
        if ($oShipping->errors()) {
            $ErrorMsg = implode(",", $oShipping->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oShipping->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function create()
    {
        //
        $Title = $this->request->getVar("Title");
        $ShippingType = $this->request->getVar("ShippingType");
        $Status = $this->request->getVar("Status");
        $StatusOutlying = $this->request->getVar("StatusOutlying");
        $ShippingFee = $this->request->getVar("ShippingFee");
        $ShippingFeeOutlying = $this->request->getVar("ShippingFeeOutlying");
        $DeliverVolumeMax = $this->request->getVar("DeliverVolumeMax");
        $DeliverWeightMax = $this->request->getVar("DeliverWeightMax");
        $DeliveryFrozen = $this->request->getVar("DeliveryFrozen");
        $QueryLink = $this->request->getVar("QueryLink");
        //開始新增
        $oShipping = new \App\Models\Shipping\Shipping();
        $oShipping->protect(false);
        $insertData = [
            "Title"=>$Title,
            "ShippingType"=>$ShippingType,
            "Status"=>$Status,
            "StatusOutlying"=>$StatusOutlying,
            "ShippingFee"=>$ShippingFee,
            "ShippingFeeOutlying"=>$ShippingFeeOutlying,
            "DeliverVolumeMax"=>$DeliverVolumeMax,
            "DeliverWeightMax"=>$DeliverWeightMax,
            "DeliveryFrozen"=>$DeliveryFrozen,
            "QueryLink"=>$QueryLink,
        ];
        $ID = $oShipping->insert($insertData);
        if ($oShipping->errors()) {
            $ErrorMsg = implode(",", $oShipping->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oShipping->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oShipping = new \App\Models\Shipping\Shipping();
        //檢查ID
        $Data = $oShipping->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oShipping->protect(false);
        $oShipping->delete($ID);
        if ($oShipping->errors()) {
            $ErrorMsg = implode(",", $oShipping->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function getTypeList()
    {
        $oShipping = new \App\Models\Shipping\Shipping();
        return $this->respond(ResponseData::success($oShipping->ShippingType));
    }
}
