<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Payment;

class LinePay
{
    public $ChannelID = "";
    public $ChannelSecret = "";
    public $isSandbox = true;
    //return
    public $ErrorMessage = "";
    public $TradeData;
    //回傳網址
//    public $SuccessReturnUrl = "https://www.kolshop.com.tw/order_create/";
//    public $CancelReturnUrl = "https://kitchen.yongxin-demo.com/order_cancel/";
//    public $IconUrlPath = "https://www.kolshop.com.tw/img/logo.png";
    public $SuccessReturnUrl = "";
    public $CancelReturnUrl = "";
    public $IconUrlPath = "";
    public $NotifyUrl = "";

    public function __construct(){
        //
        $this->ChannelID = $_ENV["LinePay.ChannelID"];
        $this->ChannelSecret = $_ENV["LinePay.ChannelSecret"];
        $this->isSandbox = $_ENV["LinePay.isSandbox"];
        //前台
        $this->SuccessReturnUrl = $_ENV["app.frontURL"].'/order_create/';
        $this->CancelReturnUrl = $_ENV["app.frontURL"].'/order_cancel/';
        $this->IconUrlPath = $_ENV["app.frontURL"].'/img/LogoLinePay.png/';
    }

    /**
     * @throws \Exception
     */
    public function getLink($Data,$Device){
        $linePay = new \yidas\linePay\Client([
            'channelId' => $this->ChannelID,
            'channelSecret' => $this->ChannelSecret,
            'isSandbox' => (bool)$this->isSandbox,
        ]);
        //
        $response = $linePay->request([
            'amount' => (int)$Data["Price"],
            'currency' => 'TWD',
            'orderId' => $Data["TradeID"], //Your order ID
            'packages' => [
                [
                    'id' => time(), //Your package ID
                    'amount' => (int)$Data["Price"],
                    'name' => '網路訂單:' . $Data["TradeID"], //Your package name
                    'products' => [
                        [
                            'name' =>  '網路訂單:' . $Data["TradeID"], //Your product name
                            'quantity' => 1,
                            'price' => (int)$Data["Price"],
                            'imageUrl' => $this->IconUrlPath,
                        ],
                    ],
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => $this->SuccessReturnUrl . $Data["TradeID"],
                'cancelUrl' => $this->CancelReturnUrl .$Data["TradeID"],
            ],
        ]);
        if (!$response->isSuccessful()) {
            throw new \Exception("ErrorCode {$response['returnCode']}: {$response['returnMessage']}");
        }
        //填入訂單對應LinePay的ID
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->update($Data["TradeID"],[
            "ThirdPartyID"=> $response->getInfo()["transactionId"],
        ]);
        //回傳連結
        if($Device=="PC"){
            return $response->getPaymentUrl("web");
        }else{
            return $response->getPaymentUrl("app");
        }
    }
    //交易成功後的頁面 要回傳到後端，後端會再跟Line溝通一次，才會完全交易成功
    public function returnSuccess($transactionId){
        $linePay = new \yidas\linePay\Client([
            'channelId' => $this->ChannelID,
            'channelSecret' => $this->ChannelSecret,
            'isSandbox' => (bool)$this->isSandbox,
        ]);
        if(!$transactionId){
            $this->ErrorMessage = "LINE Pay 交易編號有誤";
            return false;
        }
        //根據 Line交易ID 更新訂單資料
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status","W");
        $oTrade->where("ThirdPartyID",$transactionId);
        $Data = $oTrade->first();
        if(!$Data){
            $this->ErrorMessage = "LINE Pay 交易編號找不到對應的訂單";
            return false;
        }
        //回覆LINE許可交易
        $response = $linePay->confirm($_GET["transactionId"], [
            "amount" => $Data["Price"],
            "currency" => 'TWD',
        ]);
        if($response->toArray()["returnCode"]!="0000"){
            //交易失敗
            $this->ErrorMessage = "LINE Pay 付款失敗";
            return false;
        }
        //更新訂單為付款成功
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->protect(false);
        $oTrade->update($Data["TradeID"],[
            "Status"=>"P",//已付款
            "PaymentTime"=>date("Y-m-d H:i:s"),//付款時間
        ]);
        //抓最新訂單資訊
        $oTrade->resetQuery();
        $this->TradeData = $oTrade->find($Data["TradeID"]);
        //
        return true;
    }


}