<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Trade extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("MemberID", $LoginMemberID);
        $oTrade->orderBy("TradeID", "DESC");
        $List = $oTrade->findAll();
        //
        $SubTradeKeyValue = [];
        $DiscountKeyValue = [];
        $CouponKeyValue = [];
        //
        if ($List) {
            //
            $TradeIDArray = array_column($List, "TradeID");
            $oSubTrade = new \App\Models\Trade\SubTrade();
            $oSubTrade->whereIn("TradeID", $TradeIDArray);
            $Temp = $oSubTrade->findAll();
            //優惠 SubTrade 有 DiscountID_PercentMenu DiscountID_PercentFull
            if ($Temp) {
                $DiscountIDArray = array_column($Temp, "DiscountID_PercentMenu");
                $DiscountIDArray = array_merge($DiscountIDArray, array_column($Temp, "DiscountID_PercentFull"));
                $DiscountIDArray = array_unique($DiscountIDArray);
                $oDiscount = new \App\Models\Discount\Discount();
                $oDiscount->whereIn("DiscountID", $DiscountIDArray);
                $TempDiscount = $oDiscount->findAll();
                $DiscountKeyValue = [];
                if ($TempDiscount) {
                    $DiscountKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKV($TempDiscount, "DiscountID");
                }
                foreach ($Temp as $key => $Data) {
                    $Temp[$key]["DiscountID_PercentMenu_Info"] = $DiscountKeyValue[$Data["DiscountID_PercentMenu"]]??[];
                    $Temp[$key]["DiscountID_PercentFull_Info"] = $DiscountKeyValue[$Data["DiscountID_PercentFull"]]??[];
                }
                $SubTradeKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "TradeID");
            }

            //優惠 Trade 有 DiscountID_Ｇ DiscountID_D
            $DiscountIDArray = array_column($List, "DiscountID_Ｇ");
            $DiscountIDArray = array_merge($DiscountIDArray, array_column($List, "DiscountID_D"));
            $DiscountIDArray = array_unique($DiscountIDArray);
            $oDiscount = new \App\Models\Discount\Discount();
            $oDiscount->whereIn("DiscountID", $DiscountIDArray);
            $TempDiscount = $oDiscount->findAll();
            if ($TempDiscount) {
                $DiscountKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKV($TempDiscount, "DiscountID");
            }
            //
            $CouponIDArray = array_column($List, "CouponID");
            $CouponIDArray = array_unique($CouponIDArray);
            $oCoupon = new \App\Models\Coupon\Coupon();
            $oCoupon->whereIn("CouponID", $CouponIDArray);
            $TempCoupon = $oCoupon->findAll();
            if ($TempCoupon) {
                $CouponKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKV($TempCoupon, "CouponID");
            }
        }
        //
        foreach ($List as $key => $Data) {
            //狀態
            $List[$key]["StatusTxt"] = \App\Models\Trade\Trade::$Status[$Data["Status"]];
            //
            $List[$key]["SubTradeList"] = $SubTradeKeyValue[$Data["TradeID"]]??[];
            //優惠 Trade 有 DiscountID_Ｇ DiscountID_D
            $List[$key]["DiscountID_Ｇ_Info"] = $DiscountKeyValue[$Data["DiscountID_Ｇ"]]??[];
            $List[$key]["DiscountID_D_Info"] = $DiscountKeyValue[$Data["DiscountID_D"]]??[];
            //折價卷
            $List[$key]["CouponInfo"] = $CouponKeyValue[$Data["CouponID"]]??[];
        }
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getPayment($TradeID)
    {
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");//只有Ｗ狀態能夠補上付款
        $oTrade->where("MemberID", $LoginMemberID);
        $TradeData = $oTrade->find($TradeID);
        if (!$TradeData) {
            return $this->respond(ResponseData::fail("交易編號錯誤"));
        }
        //
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->where("TradeID", $TradeData["TradeID"]);
        $SubTradeList = $oSubTrade->findAll();
        //整理金流 回傳的連結 更改特定貨到付款方式的訂單Status
        $oLibPayment = new \App\Libraries\Payment\Payment();
        $PaymentHTML = $oLibPayment->getLinkHTML($TradeData);
        //Res
        $ResData = [
            "TradeData"=>$TradeData,
            "SubTradeList"=>$SubTradeList,
            "PaymentHTML"=>$PaymentHTML
        ];
        return $this->respond(ResponseData::success($ResData));
    }
    public function cancel($TradeID)
    {
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");//只有Ｗ狀態能夠補上付款
        $oTrade->where("MemberID", $LoginMemberID);
        $TradeData = $oTrade->find($TradeID);
        if (!$TradeData) {
            return $this->respond(ResponseData::fail("交易編號錯誤"));
        }
        //取消訂單、返回庫存
        $oTrade->resetQuery();
        $oTrade->cancelAndStockBack($TradeID);
        //
        return $this->respond(ResponseData::success([]));
    }
    public function getHCTInfo($TradeID)
    {
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oTrade = new \App\Models\Trade\Trade();
//        $oTrade->whereIn("Status",["S","A","F"]);//只能查出貨後的訂單情況
        $oTrade->where("MemberID", $LoginMemberID);
        $TradeData = $oTrade->find($TradeID);
        if (!$TradeData) {
            return $this->respond(ResponseData::fail("交易編號錯誤或訂單尚未出貨"));
        }
        if ($TradeData["ShippingID"]!="5") {
            return $this->respond(ResponseData::fail("運送方式不是新竹貨運"));
        }
        if (!in_array($TradeData["Status"], ["S","A","F"], true)) {
            return $this->respond(ResponseData::fail("訂單尚未出貨，無法查詢貨況"));
        }
        if (!$TradeData["ShippingCode"]) {
            return $this->respond(ResponseData::fail("訂單尚未設定貨運編號"));
        }
        //查詢
        $oLibHCT = new \App\Libraries\HCT\HCT();
        $ShippingInfo = $oLibHCT->getShippingInfo($TradeData["ShippingCode"]);
        $ShippingInfo = str_replace(["\r", "\n", "\r\n", "\n\r","  "], '', $ShippingInfo);//去除無用字元
        //Res
        return $this->respond(ResponseData::success($ShippingInfo));
    }
}
