<?php

namespace App\Controllers\Admin\Coupon;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Coupon extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $oCoupon = new \App\Models\Coupon\Coupon();
        $oCoupon->orderBy("CouponID", "DESC");
        $List = $oCoupon->findAll();
        //
        $TradeKeyValue = [];
        if ($List) {
            //訂單使用紀錄
            $FilterIDArray = array_column($List, "CouponID");
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->where("CouponID <>", "0");
            $oTrade->whereIn("CouponID", $FilterIDArray);
            $Temp = $oTrade->findAll();
            $TradeKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "CouponID");
        }
        //
        foreach ($List as $key=>$value) {
            $List[$key]["TradeList"] = $TradeKeyValue[$value["CouponID"]]??[];
        }
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $CouponNumber = $this->request->getVar("CouponNumber");
        $Title = $this->request->getVar("Title");
        $Money = $this->request->getVar("Money");
        $LimitMember = $this->request->getVar("LimitMember");
        $OnlyMember = $this->request->getVar("OnlyMember");
        $Threshold = $this->request->getVar("Threshold");
        $StartTime = $this->request->getVar("StartTime");
        $EndTime = $this->request->getVar("EndTime");
        $Status = $this->request->getVar("Status");
        $CouponCount = $this->request->getVar("CouponCount");
        //
        $oCoupon = new \App\Models\Coupon\Coupon();
        $oCoupon->protect(false);
        $CouponID = $oCoupon->insert([
            "CouponNumber"=>$CouponNumber,
            "Title"=>$Title,
            "Money"=>$Money,
            "LimitMember"=>$LimitMember,
            "OnlyMember"=>$OnlyMember,
            "Threshold"=>$Threshold,
            "StartTime"=>$StartTime,
            "EndTime"=>$EndTime,
            "Status"=>$Status,
            "CouponCount"=>$CouponCount,
        ]);
        if ($oCoupon->errors()) {
            $ErrorMsg = implode(",", $oCoupon->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oCoupon->find($CouponID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $CouponNumber = $this->request->getVar("CouponNumber");
        $Title = $this->request->getVar("Title");
        $Money = $this->request->getVar("Money");
        $LimitMember = $this->request->getVar("LimitMember");
        $OnlyMember = $this->request->getVar("OnlyMember");
        $Threshold = $this->request->getVar("Threshold");
        $StartTime = $this->request->getVar("StartTime");
        $EndTime = $this->request->getVar("EndTime");
        $Status = $this->request->getVar("Status");
        $CouponCount = $this->request->getVar("CouponCount");
        //
        $oCoupon = new \App\Models\Coupon\Coupon();
        //檢查ID
        $Data = $oCoupon->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oCoupon->protect(false);
        $updateData = [
            "CouponNumber"=>$CouponNumber,
            "Title"=>$Title,
            "Money"=>$Money,
            "LimitMember"=>$LimitMember,
            "OnlyMember"=>$OnlyMember,
            "Threshold"=>$Threshold,
            "StartTime"=>$StartTime,
            "EndTime"=>$EndTime,
            "Status"=>$Status,
            "CouponCount"=>$CouponCount,
        ];
        $oCoupon->update($ID, $updateData);
        if ($oCoupon->errors()) {
            $ErrorMsg = implode(",", $oCoupon->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oCoupon->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oCoupon = new \App\Models\Coupon\Coupon();
        //檢查ID
        $Data = $oCoupon->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //刪除原本圖檔
        if (isset($Data["Image1"]) && $Data["Image1"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image1"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除DB
        $oCoupon->protect(false);
        $oCoupon->delete($ID);
        if ($oCoupon->errors()) {
            $ErrorMsg = implode(",", $oCoupon->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
