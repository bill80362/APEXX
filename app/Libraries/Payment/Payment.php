<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Payment;

class Payment
{
    /**
     * @throws \Exception
     */
    public function getLinkHTML($TradeInsertData, $Device="PC"){
        $PaymentHTML = "";
        $oPayment = new \App\Models\Payment\Payment();
        $PaymentData = $oPayment->find($TradeInsertData["PaymentID"]);
        if($PaymentData){
            if( in_array($PaymentData["PaymentID"],["Credit","ATM","CVS"]) ){
                //綠界信用卡、ATM、超商代碼
                $oLibECPay = new \App\Libraries\Payment\ECPay();
                $PaymentHTML = $oLibECPay->getHTML_AIO($TradeInsertData,$PaymentData);
            }elseif( in_array($PaymentData["PaymentType"],["FAMIC2C","UNIMARTC2C"]) ){
                //綠界 貨到付款 全家 7-11
                $oLibECPay = new \App\Libraries\Payment\ECPay();
                $PaymentHTML = $oLibECPay->getHTML_CVS($TradeInsertData,$PaymentData);
            }elseif( in_array($PaymentData["PaymentType"],["JKO"]) ){
                //街口支付
                $oLibJkoPay = new \App\Libraries\Payment\JkoPay();
                $LinkURL = $oLibJkoPay->getLink($TradeInsertData,$Device);
                if(!$LinkURL) throw new \Exception($oLibJkoPay->ErrorMessage);
                $PaymentHTML = $LinkURL;
            }elseif( in_array($PaymentData["PaymentType"],["LINEPAY"]) ){
                //LinePay
                $oLibLinePay = new \App\Libraries\Payment\LinePay();
                $LinkURL = $oLibLinePay->getLink($TradeInsertData,$Device);
                if(!$LinkURL) throw new \Exception($oLibLinePay->ErrorMessage);
                $PaymentHTML = $LinkURL;
            }elseif( in_array($PaymentData["PaymentType"],["PCHomeCredit"]) ){
                $oLib = new \App\Libraries\Payment\PCHomePay();
                $LinkURL = $oLib->getLink($TradeInsertData,$Device,["CARD"]);
                if(!$LinkURL) throw new \Exception($oLib->ErrorMessage);
                $PaymentHTML = $LinkURL;
            }elseif( in_array($PaymentData["PaymentType"],["PCHomeATM"]) ){
                $oLib = new \App\Libraries\Payment\PCHomePay();
                $LinkURL = $oLib->getLink($TradeInsertData,$Device,["ATM"]);
                if(!$LinkURL) throw new \Exception($oLib->ErrorMessage);
                $PaymentHTML = $LinkURL;
            }elseif( in_array($PaymentData["PaymentType"],["NormalATM"]) ){
                $PaymentHTML = "";
                //
                $oDataColumn = new \App\Models\DataColumn();
                //交易等待中、ＡＴＭ更新帳號
                $oTrade = new \App\Models\Trade\Trade();
                $oTrade->protect(false);
                $oTrade->update($TradeInsertData["TradeID"],[
                    "ThirdPartyData" => $oDataColumn->find("ATMInfo")["Content"]??"",
                ]);
                /**訂單產生通知**/
                $oLibMail = new \App\Libraries\Tools\Mail();
                $notifyShipping = $oLibMail->notifyTradeSuccess($TradeInsertData["TradeID"]);
            }
        }
        //
        return $PaymentHTML;
    }


}