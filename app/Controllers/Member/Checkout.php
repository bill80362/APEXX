<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Exceptions\RtnException;

class Checkout extends BaseController
{
    use ResponseTrait;
    //地圖
    public function get711Map()
    {
        $oLibECPay = new \App\Libraries\Payment\ECPay();
        $PaymentHTML = $oLibECPay->getCvsMap("UNIMARTC2C");
        //
        $ResData = [
            "PaymentHTML"=>$PaymentHTML,
        ];
        return $this->respond(ResponseData::success($ResData));
    }
    public function getFamilyMap()
    {
        $oLibECPay = new \App\Libraries\Payment\ECPay();
        $PaymentHTML = $oLibECPay->getCvsMap("FAMIC2C");
        //
        $ResData = [
            "PaymentHTML"=>$PaymentHTML,
        ];
        return $this->respond(ResponseData::success($ResData));
    }
    //收銀台 計算訂單
    public function cashier()
    {
        $CouponCode = $this->request->getVar("CouponCode");//折扣碼
        $PaymentID = $this->request->getVar("PaymentID");//金流
        $ShippingID = $this->request->getVar("ShippingID");//物流
        $ShoppingCart = $this->request->getJsonVar("ShoppingCart", true);//購物車
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //購物車計算
//        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
//        $oShoppingCart->where("ShoppingCart.MemberID",$LoginMemberID);
//        $ShopCartList = $oShoppingCart->findAll();
        //
        $oLibCheckout = new \App\Libraries\Checkout($LoginMemberID);
        $CheckoutList = $oLibCheckout->cashier($ShoppingCart, $CouponCode, $PaymentID, $ShippingID);
        if (!$CheckoutList) {
            return $this->respond(ResponseData::fail($oLibCheckout->ErrorMessage));
        }
        //Res
        $ResData = [
            //優惠
            "DiscountMenuTotal"=>$oLibCheckout->DiscountMenuTotal,
            "DiscountFullTotal"=>$oLibCheckout->DiscountFullTotal,
            "AfterCouponTotal"=>$oLibCheckout->AfterCouponTotal,
            "ShippingFree"=>$oLibCheckout->ShippingFree,
            "DiscountID_D"=>$oLibCheckout->DiscountID_ShippingFree,
            "GiveInfo"=>$oLibCheckout->GiveInfo,
            "CouponInfo"=>$oLibCheckout->CouponInfo,
            //金流
            "PaymentInfo"=>$oLibCheckout->PaymentInfo,
            "PaymentSubtotalFee"=>$oLibCheckout->PaymentSubtotalFee,
            //物流
            "ShippingFee"=>$oLibCheckout->ShippingFee,
            "ShippingFeeOutlying"=>$oLibCheckout->ShippingFeeOutlying,
            "ShippingStatusOutlying"=>$oLibCheckout->ShippingStatusOutlying,
            "TotalDeliverWeight"=>$oLibCheckout->TotalDeliverWeight,
            "TotalDeliverVolume"=>$oLibCheckout->TotalDeliverVolume,
            //訂單列表
            "CheckoutList" =>$oLibCheckout->CheckoutList,
            //最終價格
            "FinalTotal" =>$oLibCheckout->FinalTotal,
            "FinalTotalOutlying" =>$oLibCheckout->FinalTotalOutlying,
        ];
        return $this->respond(ResponseData::success($ResData));
    }
    //產生訂單
    public function checkout()
    {
        //街口支付和LinePay
        $Device = $this->request->getVar("Device");//折扣碼
        //結帳購物車
        $CouponCode = $this->request->getVar("CouponCode");//折扣碼
        $PaymentID = $this->request->getVar("PaymentID");//金流
        $ShippingID = $this->request->getVar("ShippingID");//物流
        $ShoppingCart = $this->request->getJsonVar("ShoppingCart", true);//購物車
        //購買人資訊
        $BuyerName = $this->request->getVar("BuyerName");//姓名
        $BuyerPhone = $this->request->getVar("BuyerPhone");//電話
        //收件人資訊
        $ReceiverName = $this->request->getVar("ReceiverName");//姓名
        $ReceiverPhone = $this->request->getVar("ReceiverPhone");//電話
        $ReceiverEmail = $this->request->getVar("ReceiverEmail");//Email
        $ReceiverAddressCode = $this->request->getVar("ReceiverAddressCode");//郵遞區號
        $ReceiverAddress = $this->request->getVar("ReceiverAddress");//郵遞區號
        $ReceiverMemo = $this->request->getVar("ReceiverMemo");//郵遞區號
        //貨到付款資訊
        $ReceiverStoreNo = $this->request->getVar("ReceiverStoreNo");
        $ReceiverStoreInfo = $this->request->getVar("ReceiverStoreInfo");
        //訂單前端備註
        $OrderMemo = $this->request->getVar("OrderMemo");
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //收銀台
        $oLibCheckout = new \App\Libraries\Checkout($LoginMemberID);
        $CheckoutList = $oLibCheckout->cashier($ShoppingCart, $CouponCode, $PaymentID, $ShippingID);
        if (!$CheckoutList) {
            return $this->respond(ResponseData::fail($oLibCheckout->ErrorMessage));
        }
        //根據郵遞區號，判斷是否外縣市
        $oZipcode = new \App\Models\Zipcode\Zipcode();
        $oZipcode->where("Zipcode", $ReceiverAddressCode);
        $ZipcodeData = $oZipcode->first();
        if (!$ZipcodeData) {
            return $this->respond(ResponseData::fail("郵遞區號錯誤"));
        }
        $isOutlying = $oZipcode->isOutlying($ZipcodeData["City"]);
        //判斷離島縣市運費
        if ($isOutlying) {
            $TradePrice = $oLibCheckout->FinalTotalOutlying;
        } else {
            $TradePrice = $oLibCheckout->FinalTotal;
        }
        //建立訂單
        $TradeInsertData = [
            "Status"=>"W",
            "MemberID"=>$LoginMemberID,
            //金流
            "PaymentID"=>$PaymentID,
            "PaymentSubtotalFee"=>$oLibCheckout->PaymentSubtotalFee,
            //物流
            "ShippingID"=>$ShippingID,
            "ShippingFee"=>$oLibCheckout->ShippingFee,
            //折價卷
            "CouponID"=>$oLibCheckout->CouponInfo["CouponID"]??0,
            "CouponMoney"=>$oLibCheckout->CouponInfo["Money"]??0,
            //贈品
            "DiscountID_Ｇ"=>$oLibCheckout->GiveInfo["DiscountID"]??0,
            "GiveName"=>$oLibCheckout->GiveInfo["GiveName"]??"",
            "GiveImage"=>$oLibCheckout->GiveInfo["Image1"]??"",
            //免運
            "DiscountID_D"=>$oLibCheckout->DiscountID_ShippingFree,
            "ShippingFree"=>$oLibCheckout->ShippingFree ? "Y" : "N",
            //價格
            "Price"=>$TradePrice,
            //購買人
            "BuyerName"=>$BuyerName,
            "BuyerPhone"=>$BuyerPhone,
            //收件人資訊
            "ReceiverName"=>$ReceiverName,
            "ReceiverPhone"=>$ReceiverPhone,
            "ReceiverEmail"=>$ReceiverEmail,
            "ReceiverAddressCode"=>$ReceiverAddressCode,
            "ReceiverAddress"=>$ReceiverAddress,
            "ReceiverMemo"=>$ReceiverMemo,
            //貨到付款
            "ReceiverStoreNo"=>$ReceiverStoreNo,
            "ReceiverStoreInfo"=>$ReceiverStoreInfo,
            //訂單前端備註
            "OrderMemo"=>$OrderMemo,
        ];
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->protect(false);
        $TradeInsertData["TradeID"] = $oTrade->insert($TradeInsertData);
        if ($oTrade->errors()) {
            $ErrorMsg = implode(",", $oTrade->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //建立子單
        $SubTradeInsertList = [];
        foreach ($CheckoutList as $Data) {
            $SubTradeInsertList[] = [
                "TradeID"=>$TradeInsertData["TradeID"],
                "Status"=>"Y",
                "GoodsID"=>$Data["GoodsID"],
                "ColorID"=>$Data["ColorID"],
                "SizeID"=>$Data["SizeID"],
                "CustomSpecID"=>$Data["CustomSpecID"],
                "DeliverWeight"=>$Data["DeliverWeight"],
                "DeliverVolume"=>$Data["DeliverVolume"],
                "DiscountID_PercentMenu"=>$Data["DiscountPercentMenuInfo"]["DiscountID"]??0,
                "DiscountPercentMenu"=>$Data["DiscountPercentMenu"],
                "DiscountID_PercentFull"=>$Data["DiscountPercentFullInfo"]["DiscountID"]??0,
                "DiscountPercentFull"=>$Data["DiscountPercentFull"],
                "SellPrice"=>$Data["SellPrice"],
                "FinalPrice"=>$Data["DiscountPrice"],
                "RefundPrice"=>$Data["RefundPrice"],
            ];
        }
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->protect(false);
        $oSubTrade->insertBatch($SubTradeInsertList);
        if ($oSubTrade->errors()) {
            $ErrorMsg = implode(",", $oSubTrade->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //訂單建立成功，清除購物車
        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
        $oShoppingCart->protect(false);
        foreach ($CheckoutList as $Data) {
            $oShoppingCart->resetQuery();
            $oShoppingCart->where("MemberID", $LoginMemberID);
            $oShoppingCart->where("GoodsID", $Data["GoodsID"]);
            if (isset($Data["IsCustom"]) && $Data["IsCustom"] == "Y") {
                // 客製化商品
                $oShoppingCart->where("CustomSpecID", $Data["CustomSpecID"]);
            } else {
                // 一般商品
                $oShoppingCart->where("ColorID", $Data["ColorID"]);
                $oShoppingCart->where("SizeID", $Data["SizeID"]);
            }
            $oShoppingCart->delete();
        }
        //扣商品庫存
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oGoodsStock->protect(false);
        foreach ($CheckoutList as $Data) {
            // 排除客製化商品
            if (isset($Data["IsCustom"]) && $Data["IsCustom"] == "Y") {
                continue;
            }
            $oGoodsStock->ioStock($Data["GoodsID"], $Data["ColorID"], $Data["SizeID"], "-1");
        }
        $oCustomGoodsStock = new \App\Models\CustomGoods\CustomGoodsStock();
        $oCustomGoodsStock->protect(false);
        foreach ($CheckoutList as $Data) {
            // 客製化商品
            if (isset($Data["IsCustom"]) && $Data["IsCustom"] == "Y") {
                $oCustomGoodsStock->ioStock($Data["GoodsID"], "-1");
            }
        }
        //扣折扣碼使用量
        $useCouponID = $oLibCheckout->CouponInfo["CouponID"]??0;
        if ($useCouponID) {
            $oCoupon = new \App\Models\Coupon\Coupon();
            $oCoupon->protect(false);
            $oCoupon->ioCount($useCouponID, "-1");
        }
        //整理金流 回傳的連結 更改特定貨到付款方式的訂單Status
        $oLibPayment = new \App\Libraries\Payment\Payment();
        try {
            $PaymentHTML = $oLibPayment->getLinkHTML($TradeInsertData, $Device);
        } catch (\Exception $e) {
            //金流網址取得失敗，取消訂單，返回庫存
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->cancelAndStockBack($TradeInsertData["TradeID"]);
            //回傳錯誤
            return $this->respond(ResponseData::fail($e->getMessage()));
        }
        /**訂單產生通知**/
//        $oPayment = new \App\Models\Payment\Payment();
//        $PaymentData = $oPayment->find($PaymentID);
//        if( in_array($PaymentData,[""]) ){
//            $oLibMail = new \App\Libraries\Tools\Mail();
//            $notifyShipping = $oLibMail->notifyTradeSuccess($TradeInsertData["TradeID"]);
//        }
        //Res
        $ResData = [
            "TradeData"=>$TradeInsertData,
            "SubTradeList"=>$SubTradeInsertList,
            "PaymentHTML"=>$PaymentHTML
        ];
        return $this->respond(ResponseData::success($ResData));
    }
}
