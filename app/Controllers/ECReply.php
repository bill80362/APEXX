<?php

namespace App\Controllers;

class ECReply extends BaseController
{
    public function getAIO()
    {
        $PostData = $this->request->getPost();
        //Log
        $oECReply = new \App\Models\ECReply\ECReply();
        $oECReply->protect(false);
        $oECReply->insert([
            "PostData"=>print_r($PostData, true),
        ]);
        //ECData decode
        $oLibECPay = new \App\Libraries\Payment\ECPay();
        $ECPayData = $oLibECPay->replyAIO($PostData);
        //Log
        $oECReply = new \App\Models\ECReply\ECReply();
        $oECReply->protect(false);
        $oECReply->insert([
            "PostData"=>print_r($ECPayData, true),
        ]);
        //不是陣列那就是錯誤訊息
        if (!is_array($ECPayData)) {
            exit('0|Error');
        }

        //處理資料
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $oTrade->whereIn("Trade.Status", ["W"]);
        $TradeData = $oTrade->find($ECPayData["MerchantTradeNo"]);
        //找不到資料
        if (!$TradeData) {
            exit('0|Error');
        }
        if ($TradeData["PaymentType"]=="ATM" && $ECPayData["RtnCode"]==2) {
            /**ATM 會員取得匯款虛擬帳號**/
            /***
             * Array (
             * [BankCode] => 118
             * [CheckMacValue] => 095F358FDD3A9390E51A0C33860B21886B99E5F043468443DEDA0DBEAD4AFC08
             * [CustomField1] =>
             * [CustomField2] =>
             * [CustomField3] =>
             * [CustomField4] =>
             * [ExpireDate] => 2022/02/12
             * [MerchantID] => 2000132
             * [MerchantTradeNo] => 546546124
             * [PaymentType] => ATM_PANHSIN
             * [RtnCode] => 2
             * [RtnMsg] => Get VirtualAccount Succeeded
             * [StoreID] =>
             * [TradeAmt] => 1220
             * [TradeDate] => 2022/02/09 09:55:26
             * [TradeNo] => 2202090955190428
             * [vAccount] => 9900140673104366
             * )
             **/
            //放上 虛擬ＡＴＭ帳號、匯款期限
            $oTrade->protect(false);
            $oTrade->update($TradeData["TradeID"], [
                "ThirdPartyID" => $ECPayData["TradeNo"],
                "ThirdPartyData" => "(" . $ECPayData["BankCode"] . ")" . $ECPayData["vAccount"] . "_" . $ECPayData["ExpireDate"],
            ]);
            /**訂單產生通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyTradeSuccess($TradeData["TradeID"]);
        } elseif ($TradeData["PaymentType"]=="CVS" && $ECPayData["RtnCode"]=="10100073") {
            /**
             * Array
            (
            [Barcode1] =>
            [Barcode2] =>
            [Barcode3] =>
            [CheckMacValue] => 95AA7A4A2117CE5A1766113513104C2181E9DBEDA87706B640B5547070BC191B
            [CustomField1] =>
            [CustomField2] =>
            [CustomField3] =>
            [CustomField4] =>
            [ExpireDate] => 2022/02/09 21:15:01
            [MerchantID] => 2000132
            [MerchantTradeNo] => 546546128
            [PaymentNo] => LLL22040676057
            [PaymentType] => CVS_CVS
            [RtnCode] => 10100073
            [RtnMsg] => Get CVS Code Succeeded.
            [StoreID] =>
            [TradeAmt] => 1205
            [TradeDate] => 2022/02/09 21:12:01
            [TradeNo] => 2202092111541688
            )
             ***/
            //放上 超商代碼繳費資訊
            $oTrade->protect(false);
            $oTrade->update($TradeData["TradeID"], [
                "ThirdPartyID" => $ECPayData["TradeNo"],
                "ThirdPartyData" => $ECPayData["PaymentNo"] . "_" . $ECPayData["ExpireDate"],
            ]);
            /**訂單產生通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyTradeSuccess($TradeData["TradeID"]);
        } elseif (in_array($TradeData["PaymentType"], ["ATM","Credit","CVS"], true) && $ECPayData["RtnCode"]==1) {
            /**
             * ATM 付款成功
            (
            [CheckMacValue] => 8FCA61C14C5DDF8D0A7F343E008BC23382506C3FAF510038C5D9AC5BE0237C6F
            [CustomField1] =>
            [CustomField2] =>
            [CustomField3] =>
            [CustomField4] =>
            [MerchantID] => 2000132
            [MerchantTradeNo] => 546546126
            [PaymentDate] => 2022/02/09 19:37:28
            [PaymentType] => ATM_TAISHIN
            [PaymentTypeChargeFee] => 0
            [RtnCode] => 1
            [RtnMsg] => 付款成功
            [SimulatePaid] => 1
            [StoreID] =>
            [TradeAmt] => 1220
            [TradeDate] => 2022/02/09 19:32:32
            [TradeNo] => 2202091932321618
            )
             **/
            /***
             * 信用卡 付款成功
            (
            [CheckMacValue] => D7E6ECC90437689B2E541705784E2D9570EFC0B2F56BA56DD5D22D8BFB41C1EB
            [CustomField1] =>
            [CustomField2] =>
            [CustomField3] =>
            [CustomField4] =>
            [MerchantID] => 2000132
            [MerchantTradeNo] => 546546127
            [PaymentDate] => 2022/02/09 21:08:20
            [PaymentType] => Credit_CreditCard
            [PaymentTypeChargeFee] => 25
            [RtnCode] => 1
            [RtnMsg] => 交易成功
            [SimulatePaid] => 0
            [StoreID] =>
            [TradeAmt] => 1230
            [TradeDate] => 2022/02/09 21:05:24
            [TradeNo] => 2202092105241682
            )

             */
            $oTrade->protect(false);
            $oTrade->update($TradeData["TradeID"], [
                "Status"=>"P",//已付款
                "PaymentTime"=>$ECPayData["PaymentDate"],//付款時間
                "ThirdPartyID"=>$ECPayData["TradeNo"],//綠界ID
            ]);
            /**付款成功通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyPaySuccess($TradeData["TradeID"]);
        }

        // 在網頁端回應 1|OK
        echo '1|OK';
        exit();
    }
    public function getLogistics()
    {
        $PostData = $this->request->getPost();
        //Log
        $oECReply = new \App\Models\ECReply\ECReply();
        $oECReply->protect(false);
        $oECReply->insert([
            "PostData"=>print_r($PostData, true),
        ]);
        //ECData decode
        $oLibECPay = new \App\Libraries\Payment\ECPay();
        $ECPayData = $oLibECPay->replyCVS($PostData);
        //Log
        $oECReply = new \App\Models\ECReply\ECReply();
        $oECReply->protect(false);
        $oECReply->insert([
            "PostData"=>print_r($ECPayData, true),
        ]);
        //處理資料
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $oTrade->whereIn("Trade.Status", ["W"]);
        $TradeData = $oTrade->find(substr($ECPayData["MerchantTradeNo"], 5));
        if (in_array($TradeData["PaymentType"], ["UNIMARTC2C","FAMIC2C"], true) && $ECPayData["RtnCode"]==300) {
            /**
             * Array
            (
            [AllPayLogisticsID] => 1865547
            [BookingNote] =>
            [CheckMacValue] => 982361A518805D6B3D4271E88AB425B9
            [CVSPaymentNo] => D8264948
            [CVSValidationNo] => 0364
            [GoodsAmount] => 3130
            [LogisticsSubType] => UNIMARTC2C
            [LogisticsType] => CVS
            [MerchantID] => 2000933
            [MerchantTradeNo] => 546546132
            [ReceiverAddress] =>
            [ReceiverCellPhone] => 0912111222
            [ReceiverEmail] => bill@gmail.com
            [ReceiverName] => 收件人名稱
            [ReceiverPhone] => 0912111222
            [RtnCode] => 300
            [RtnMsg] => 訂單處理中(已收到訂單資料)
            [UpdateStatusDate] => 2022/02/09 22:11:18
            )
             **/
            $oTrade->protect(false);
            //物流要使用的列印編號和驗證碼
            // Fami 'CVSPaymentNo' => '11000062128',
            // Unimart 'CVSPaymentNo' => 'C9680734','CVSValidationNo' => '4551',
            $ThirdPartyData = $ECPayData["CVSPaymentNo"]."_".$ECPayData["CVSValidationNo"];
            //
            $oTrade->update($TradeData["TradeID"], [
                "Status"=>"T",//理貨中
                "ThirdPartyID"=>$ECPayData["AllPayLogisticsID"],//綠界ID
                "ThirdPartyData"=>$ThirdPartyData,
            ]);
            /**付款成功通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyPaySuccess($TradeData["TradeID"]);
        }
        // 在網頁端回應 1|OK
        echo '1|OK';
        exit();
    }
    //EC 取Map資訊 POST轉GET
    public function redirectMapInfo()
    {
        helper('url');
        $PostData = $this->request->getPost();
        $Attr = [];
        foreach ($PostData as $key => $value) {
            $Attr[] = $key."=".$value;
        }
        $redirectURL = $_ENV["app.frontURL"]."/cart?".implode("&", $Attr);
        return redirect()->to($redirectURL);
    }
}
