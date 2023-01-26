<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Payment;

class PCHomePay
{
    //測試會員資料
    //帳號: test_buyer
    //密碼: test_buyer
    //
    //測試信用卡資料
    //成功卡號: 4013-5243-8125-0527
    //失敗卡號: 4013-5243-8125-0469
    //有效期限(月/年): 12/30
    //安全碼: 999

    //正式
    public $ChannelID = "";
    public $ChannelSecret = "";
    public $SiteUrl = "https://api.pchomepay.com.tw";

    //
    public $PreCode = "AAAAA";//會轉成隨機alnum
    //return
    public $Token = "";
    public $ErrorMessage = "";
    public $TradeData;
    //回傳網址
    public $SuccessReturnUrl = "";
    public $CancelReturnUrl = "";
    public $IconUrlPath = "";
    public $NotifyUrl = "";
    public function __construct()
    {
        //
        $this->ChannelID = $_ENV["PCHomePay.ChannelID"];
        $this->ChannelSecret = $_ENV["PCHomePay.ChannelSecret"];
        $this->SiteUrl = $_ENV["PCHomePay.SiteUrl"];
        //前台
        $this->SuccessReturnUrl = $_ENV["app.frontURL"].'/order_create/';
        $this->CancelReturnUrl = $_ENV["app.frontURL"].'/order_cancel/';
        $this->IconUrlPath = $_ENV["app.frontURL"].'/img/LogoPCHomePay.png/';
        //後台
        $this->NotifyUrl = base_url().'/PCHome/notify/';
        //隨機前置碼
        helper('text');
        $PreCodeLength = strlen($this->PreCode);
        $this->PreCode = random_string('alnum', $PreCodeLength);
    }
    /**
     * 更新訂單狀態
     * @throws \Exception
     */
    public function updateTrade($TradeID_R)
    {
        //
        $oTrade = new \App\Models\Trade\Trade();
        $TradeData = $oTrade->where("TradeID_R", $TradeID_R)->first();
        //
        $Response = $this->getTradeStatus($TradeData["TradeID_R"]);
        //抓訂單資訊
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");
        $oTrade->where("TradeID", $TradeData["TradeID"]);
        $Data = $oTrade->first();
        if (!$Data) {
            return false;
        }
        //
        if ($Response["status"]=="W" && $Response["pay_type"]=="ATM" && isset($Response["payment_info"]) && isset($Response["payment_info"]["bank_code"])) {
            //到期時間格式
            $DateTimeString = substr($Response["payment_info"]["expire_date"], 0, 4)."-";
            $DateTimeString .= substr($Response["payment_info"]["expire_date"], 4, 2)."-";
            $DateTimeString .= substr($Response["payment_info"]["expire_date"], 6, 2)." ";
            $DateTimeString .= substr($Response["payment_info"]["expire_date"], 8, 2).":";
            $DateTimeString .= substr($Response["payment_info"]["expire_date"], 10, 2).":";
            $DateTimeString .= substr($Response["payment_info"]["expire_date"], 12, 2);
            $ExpiredDatetime = $DateTimeString;
            //交易等待中、ＡＴＭ更新帳號
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->protect(false);
            $oTrade->update($Data["TradeID"], [
                "ThirdPartyID" => $Response["order_id"],
                "ThirdPartyData" => "(" . $Response["payment_info"]["bank_code"] . ")" . $Response["payment_info"]["bank_code"] . "_" . $ExpiredDatetime,
            ]);
            /**訂單產生通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyTradeSuccess($TradeData["TradeID"]);
        } elseif ($Response["status"]=="S") {
            //更新訂單為付款成功
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->protect(false);
            $oTrade->update($Data["TradeID"], [
                "Status"=>"P",//已付款
                "PaymentTime"=>date("Y-m-d H:i:s"),//付款時間
            ]);
            /**付款成功通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyPaySuccess($TradeData["TradeID"]);
        } elseif ($Response["status"]=="F") {
            //失敗 返回庫存
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->cancelAndStockBack($Data["TradeID"]);
        }
        return true;
    }
    /**
     * 成立訂單
     * @throws \Exception
     */
    public function getLink($Data, $Device, $Type_Array)
    {
        helper('text');
        //補上隨機前置碼
        $TradeID_R = $this->PreCode.$Data["TradeID"];
        //
        $ResponseURL = $this->createOrder($TradeID_R, (int)$Data["Price"], $Type_Array);
        if (!$ResponseURL) {
            $this->ErrorMessage = "PCHome支付連線發生錯誤";
            return false;
        }
        //
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->protect(false);
        $oTrade->update($Data["TradeID"], [
            "TradeID_R" => $TradeID_R,
        ]);
        //
        return $ResponseURL;
    }
    /**
     * @param string[] $Data
     * @param string[] $Type_Array CARD,ATM
     * @throws \Exception
     * {"order_id":"A1234565","payment_url":"https://sandbox-secure.pchomepay.com.tw/apipay/ppwf?_pwfkey_=eHhWaTAzQTh5WkE1LGFELVJDQm1CbVhzTEtRemxGdzBZNTE2cHdOMENVY1dvbU5xa2JGQkpYeEVCUWlLaDZXaA=="}
     * {"error_type":"invalid_request_error","code":20001,"message":"order id duplicate "}
     */
    public function createOrder($TradeID, $Price, array $Type_Array=["CARD"])
    {
        //token
        $this->getToken();
        $headers = [
            'pcpay-token: '.$this->Token,
        ];
        //必填
        $PostData = [
            'order_id' => $TradeID, //Your order ID
            "pay_type" => $Type_Array,
            'amount' => (int)$Price,
            'return_url' => $this->SuccessReturnUrl . $TradeID,
            'fail_return_url' => $this->CancelReturnUrl .$TradeID,
            'notify_url' => $this->NotifyUrl .$TradeID,
            "items" => [
                [
                    "name"=> "網路訂單:".$TradeID,
                    "url" => $TradeID,
                ]
            ],
        ];
        //ATM 必填
        if (in_array($Type_Array, ["ATM"], true)) {
            $PostData["atm_info"] = [
                "expire_days" => 3,
            ];
        }
        //信用卡 選填 分期 不分期可以不傳
        if (in_array($Type_Array, ["CARD"], true)) {
            $PostData["card_info"] = [
                ["installment" => "3",],
                ["installment" => "6",],
                ["installment" => "18",],
            ];
        }

        $Response = $this->curl($this->SiteUrl.'/v1/payment', $PostData, $headers);
        $Response["payment_url"] = $Response["payment_url"]??"";
        if (!$Response["payment_url"]) {
            $Response['code'] = $Response['code']??"";
            $Response['message'] = $Response['message']??"";
            throw new \Exception("ErrorCode {$Response['code']}: {$Response['message']}");
        }
        //填入訂單對應LinePay的ID
        return $Response["payment_url"];
    }
    /**
     * 抓訂單資訊
    */
    public function getTradeStatus($TradeID_R)
    {
        //token
        $this->getToken();
        $headers = [
            'pcpay-token: '.$this->Token,
        ];
        $Response = $this->curl($this->SiteUrl.'/v1/payment/'.$TradeID_R, [], $headers, "GET");
        $Response["order_id"] = $Response["order_id"]??"";
        if (!$Response["order_id"]) {
            $Response['code'] = $Response['code']??"";
            $Response['message'] = $Response['message']??"";
            throw new \Exception("ErrorCode {$Response['code']}: {$Response['message']}");
        }
        return $Response;
    }

    //{"token":"zHm67sQRuPSO__eiuy2h_lEgtPlS12aVqrcVz3Kc","expired_in":28800,"expired_timestamp":1474470110}
    //{"error_type":"auth_error","code":10001,"message":"invalid user password "}
    public function getToken()
    {
        $headers = [
            'Authorization: Basic '.base64_encode($this->ChannelID.":".$this->ChannelSecret),
        ];
        $PostData = [];
        $Response = $this->curl($this->SiteUrl.'/v1/token', $PostData, $headers);
        $Response["token"] = $Response["token"]??"";
        if (!$Response["token"]) {
            $Response["error_type"] = $Response["error_type"]??"";
            $this->ErrorMessage = "支付Token取得有誤".$Response["error_type"];
            return false;
        }
        $this->Token = $Response["token"];
        return true;
    }
    public function curl($Url, $PostData, $HeaderExtra, $Method="POST")
    {
        $headers = [];
        $headers = array_merge($headers, $HeaderExtra);

        $ch = curl_init();
        if ($Method!="GET") {
            //POST
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($PostData));
        }
        //Headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);

        return $result;
    }
}
