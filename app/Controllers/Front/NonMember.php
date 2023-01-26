<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class NonMember extends BaseController
{
    use ResponseTrait;
    public function getTrade()
    {
        //
        $TradeID = $this->request->getVar("TradeID");
        $ReceiverPhone = $this->request->getVar("ReceiverPhone");
        //
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("MemberID", "0");//非會員
        $oTrade->where("ReceiverPhone", $ReceiverPhone);
        $Data = $oTrade->find($TradeID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("查無訂單資訊"));
        }
        //
        $List = [];
        $List[] = $Data;
        //
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
}
