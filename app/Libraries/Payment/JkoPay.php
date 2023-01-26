<?php

namespace App\Libraries\Payment;

class JkoPay
{
    //【測試】接口資料
//    public $URL = "https://uat-onlinepay.jkopay.app";//網址
//    public $StoreID = "ac1dfc82-bd43-11eb-bd7b-0050568403ed";//LADYMINT商號
//    public $APIKey = "JpNIM7o6TIlunFyNAJiQFKsDyoYA36HnkwO0";//
//    public $SecretKey = "j7c_gkZ342fLfK3n8ov4or225ntTu2MPA5pm7WFrzJX204ttg69e0mQPZ31wJc4QMRyJ2amyjCv1r61VE7JZKg";//
    //【正式】接口資料
    public $URL = "https://onlinepay.jkopay.com";//網址
    public $StoreID = "";//商號ID
    public $APIKey = "";//
    public $SecretKey = "";//

    //交易成功，回傳通知給後端
    //回傳網址
    public $SuccessReturnUrl = "";
    public $CancelReturnUrl = "";
    public $IconUrlPath = "";
    public $NotifyUrl = "";
//    public $SuccessReturnUrl = "https://kitchen.yongxin-demo.com/JKOPay/notify";
    //
    protected $ch;
    public $PreCode = "AAAAA";//會轉成隨機alnum
    public $ErrorMessage = "";
    public function __construct()
    {
        $this->ch = curl_init();
        //帳號密碼
        $this->URL = $_ENV["jkopay.URL"];
        $this->StoreID = $_ENV["jkopay.StoreID"];
        $this->APIKey = $_ENV["jkopay.APIKey"];
        $this->SecretKey = $_ENV["jkopay.SecretKey"];
        //前台
        $this->SuccessReturnUrl = $_ENV["app.frontURL"].'/order_create/';
        $this->CancelReturnUrl = $_ENV["app.frontURL"].'/order_cancel/';
        $this->IconUrlPath = $_ENV["app.frontURL"].'/img/LogoJkoPay.png/';
        //
        helper('text');
        $this->PreCode = random_string('alnum', 5);
    }
    public function __destruct()
    {
        curl_close($this->ch);
    }
    //
    public function getLink($Data, $Device)
    {
        //
        $ThirdPartyID = $this->PreCode.$Data["TradeID"];
        //JKO
        $JkoResponse = $this->createOrder($ThirdPartyID, (int)$Data["Price"]);
        if (!$JkoResponse) {
            $this->ErrorMessage = "街口支付連線發生錯誤";
            return false;
        }
        //填入訂單對應LinePay的ID
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->update($Data["TradeID"], [
            "ThirdPartyID"=> $ThirdPartyID,
        ]);
        //
        if ($Device=="PC") {
            return $JkoResponse["result_object"]["payment_url"];
        } else {
            return $JkoResponse["result_object"]["qr_img"];
        }
    }
    //通用Curl
    public function goCURL($Path, $Method, $PostData = [])
    {
        //DIGEST
        //1. 將字串 的 request payload以 UTF-8編 碼。
        //2. 將街口提供 串接使用 的 Secret key以 UTF-8編碼 。
        //3. 將步驟 1產生的 字節 透過 HMAC-SHA256 演算法，以 步驟 2的字節 作為秘密鑰匙進行 加簽 ，即產生 hexdigest作為 digest。
        $PostDataTxt = json_encode($PostData);
//        $this->SecretKey = utf8_encode($this->SecretKey);
        $DIGEST = hash_hmac('sha256', $PostDataTxt, $this->SecretKey);
        //
        $Header = [
            'Content-type: application/json',
            'API-KEY: '.$this->APIKey,
            'DIGEST: '.$DIGEST,
        ];
        curl_setopt($this->ch, CURLOPT_URL, $this->URL.$Path);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $Header);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt( $this->ch , CURLOPT_HEADER, true);
        if ($Method=="POST") {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $PostDataTxt);
        } else {
            curl_setopt($this->ch, CURLOPT_POST, false);
        }
        $rs = curl_exec($this->ch);
        $rs = json_decode($rs, true);
        return $rs;
    }
    //3.2街口支付訂單 創建 Entry API
    public function createOrder($OrderID, $Price)
    {
        $PostData = [
            //(必要參數)
            "platform_order_id"=> $OrderID,//電商平台端交易序號需為唯一值，不可重複
            "store_id"=> $this->StoreID,//商店編號
            "currency"=>"TWD",
            "total_price"=> $Price,//訂單原始金額
            "final_price"=> $Price,//訂單實際消費金額
            //(非必要)買家在街口確認付款頁面輸入密碼後，街口服務器訪問此電商平台服務器網址確認訂單正確性與存貨彈性。
//            "confirm_url"=>"",
            //(非必要)消費者付款完成後，街口服務器訪問此電商平台服務器網址，並在參數中提供街口交易序號與訂單交易狀態代碼。
            "result_url"=>$this->SuccessReturnUrl,//交易成功後，街口回傳訊息通知
            //(非必要)消費者付款完成後點選完成按鈕，將消費者導向此電商平台客戶端付款結果頁網址。
//            "result_display_url"=>"https://www.ladymint.com/credit/jkopay_success.php",
        ];
        return $this->goCURL("/platform/entry", "POST", $PostData);
    }
}
