<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Payment;

use Ecpay\Sdk\Exceptions\RtnException;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use Ecpay\Sdk\Services\UrlService;

use function PHPUnit\Framework\throwException;

class ECPay
{
    //EC測試用 金流AIO帳號 + 物流 B2C/Home
//    public $MerchantID = "2000132";
//    public $HashKey= "5294y06JbISpM5x9";
//    public $HashIV= "v77hoKGq4kWxNNIS";
//    public $ECURL = "https://payment-stage.ecpay.com.tw";
//    public $LogisticsURL = "https://logistics-stage.ecpay.com.tw";
    //EC測試用 物流 B2C/Home
    // 廠商管理後台 登入帳號：LogisticsC2Ctest 密碼：test1234
    // 身分證件末四碼 統一編號 59780857
//    public $MerchantID = "2000933";
//    public $HashKey= "XBERn1YOvpM9nfZc";
//    public $HashIV= "h1ONHk4P4yqbl5LK";
//    public $ECURL = "https://payment-stage.ecpay.com.tw";
//    public $LogisticsURL = "https://logistics-stage.ecpay.com.tw";
    public $MerchantID = "";
    public $HashKey= "";
    public $HashIV= "";
    public $ECURL = "";
    public $LogisticsURL = "";

    //綠界 寄件人:公司資料、商品資料
    public $GoodsName = "購買商品";
    public $SenderName = "公司名稱";
    public $SenderCellPhone = "0900111222";

    public $factory;
    public $PreCode = "AAAAA";

    public function __construct()
    {
        //帳號資訊
        $this->MerchantID = $_ENV["ecpay.MerchantID"];
        $this->HashKey = $_ENV["ecpay.HashKey"];
        $this->HashIV = $_ENV["ecpay.HashIV"];
        $this->ECURL = $_ENV["ecpay.ECURL"];
        $this->LogisticsURL = $_ENV["ecpay.LogisticsURL"];
        $this->GoodsName = $_ENV["ecpay.GoodsName"];
        $this->SenderName = $_ENV["ecpay.SenderName"];
        $this->SenderCellPhone = $_ENV["ecpay.SenderCellPhone"];
        //隨機前置碼
        helper('text');
        $PreCodeLength = strlen($this->PreCode);
        $this->PreCode = random_string('alnum', $PreCodeLength);
    }

    public function getCvsMap($LogisticsSubType)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
                'hashMethod' => 'md5',
            ]);
            $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

            $input = [
                'MerchantID' => $this->MerchantID,
                'MerchantTradeNo' => $this->PreCode.time(),
                'LogisticsType' => EcpayLogisticsType::CVS,
                'LogisticsSubType' => $LogisticsSubType,//UNIMART 統一超商 EcpayLogisticsSubType::UNIMART_C2C
                'IsCollection' => EcpayIsCollection::YES,//是否代收貨款
//                'Device' => EcpayDevice::PC, //Mobile或PC 7-11有差 全家沒差
//                'ExtraData' => '測試額外資訊',//供廠商傳遞保留的資訊，在回傳參數中，會原值回傳。

                // 請參考 example/Logistics/Domestic/GetMapResponse.php 範例開發
//                'ServerReplyURL' => 'https://www.kolshop.com.tw/cart',//選完超商後，導回的網址
                'ServerReplyURL' => base_url() . '/ECPay/map/redirect',//選完超商後，導回的網址
            ];

            $action = $this->LogisticsURL.EcpayUrl::CVS_MAP;

            return $autoSubmitFormService->generate($input, $action);
        } catch (RtnException $e) {
            return '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
        }
    }
    public function getHTML_CVS($Data, $PaymentData)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
                'hashMethod' => 'md5',
            ]);
            $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

            //訂單價格只能1~20000
            if (!((int)$Data["Price"]>=1 && (int)$Data["Price"]<=20000)) {
                throw new \Exception('訂單金額只能在1~20000元');
            }
            //
            $input = [
                'MerchantID' => $this->MerchantID,
                'MerchantTradeNo' => $this->PreCode.$Data["TradeID"],
                'MerchantTradeDate' => date('Y/m/d H:i:s'),
                'LogisticsType' => EcpayLogisticsType::CVS,
                'LogisticsSubType' => EcpayLogisticsSubType::UNIMART_C2C,//UNIMART 統一超商
                'GoodsAmount' => (int)$Data["Price"],//商品金額1~20000,
                'CollectionAmount' => (int)$Data["Price"], //代收金額
                'IsCollection' => EcpayIsCollection::YES,//是否代收貨款
                //寄件人:公司資料
                'GoodsName' => "訂單編號:".$Data["TradeID"],
                'SenderName' => $this->SenderName,
                'SenderPhone' => '0412345678',
                'SenderCellPhone' => '0912123456',
                //收件人:
                'ReceiverName' => $Data["ReceiverName"],
                'ReceiverPhone' => $Data["ReceiverPhone"],
                'ReceiverCellPhone' => $Data["ReceiverPhone"],//注意事項:只允許數字、10 碼、09 開頭
                'ReceiverEmail' => $Data["ReceiverEmail"],

                'TradeDesc' => "訂單編號:".$Data["TradeID"],

                'ServerReplyURL' => base_url().'/ECPay/notify/ServerReplyLogistics',//物流狀態都會透過此URL通知
                'ClientReplyURL' => 'https://www.kolshop.com.tw/order_create/'.$Data["TradeID"],//按下按鈕過去結帳後，會先過去EC，再馬上回來的路徑，背景作業可以不填
                'LogisticsC2CReplyURL' => base_url() . '/api/ServerReplyLogisticsStore',//當 User 選擇取貨門市有問題時，會透過此 URL 通知特店，請特店通知 User 重新選擇門市。

                'Remark' => "訂單編號:".$Data["TradeID"],//這邊放入產品細目
                'PlatformID' => '',//特約合作平台商代號

                // 請參考 example/Logistics/Domestic/GetMapResponse.php 範例取得
                'ReceiverStoreID' => $Data["ReceiverStoreNo"],
                'ReturnStoreID' => '',//退貨門市代號
            ];
            $action = $this->LogisticsURL.EcpayUrl::SHIPPING_ORDER;

            return $autoSubmitFormService->generate($input, $action);
        } catch (\Exception $e) {
//            return '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
            return $e->getMessage();
        }
    }
    public function getHTML_AIO($Data, $PaymentData)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
//                'hashMethod' => 'md5',
            ]);
            $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

            //訂單價格只能1~20000
//            if( !((int)$Data["Price"]>=1 && (int)$Data["Price"]<=20000) ){
//                throw new \Exception('訂單金額只能在1~20000元');
//            }
            //
            $input = [
                'MerchantID' => $this->MerchantID,
                'MerchantTradeNo' => $this->PreCode.$Data["TradeID"],
                'MerchantTradeDate' => date('Y/m/d H:i:s'),
                'PaymentType' => 'aio',
                'TotalAmount' => (int)$Data["Price"],
                'TradeDesc' => UrlService::ecpayUrlEncode('網路購買商品'),
                'ItemName' => "訂單編號:".$Data["TradeID"],
                'ReturnURL' => base_url().'/ECPay/notify/ServerReplyAIO',
                'ChoosePayment' => $PaymentData["PaymentType"],
                'EncryptType' => 1,
            ];
            //不同付款方式、不同參數
            if ($PaymentData["PaymentType"]=="ATM") {
                //導回前端的網址
                $input["ClientBackURL"] = "https://www.kolshop.com.tw/order_create/".$Data["TradeID"];
                $input["OrderResultURL"] = "https://www.kolshop.com.tw/order_create/".$Data["TradeID"];
                //繳費期限 (預設3天，最長60天，最短1天)
                $input["ExpireDate"] = 3;
                //伺服器端回傳付款相關資訊
                $input["PaymentInfoURL"] = base_url().'/ECPay/notify/ServerReplyAIO';
            } elseif ($PaymentData["PaymentType"]=="CVS") {
                //CVS:以分鐘為單位
                //BARCODE:以天為單位
                $input["StoreExpireDate"] = 4320;//4320分鐘=3天
                //
                $input["Desc_1"] = "網路購買商品";
                $input["Desc_2"] = "訂單編號:".$Data["TradeID"];
                $input["Desc_3"] = "";
                $input["Desc_4"] = "";
                //伺服器端回傳付款相關資訊
                $input["PaymentInfoURL"] = base_url().'/ECPay/notify/ServerReplyAIO';
            } elseif ($PaymentData["PaymentType"]=="Credit") {
                //導回前端的網址
                $input["ClientBackURL"] = "https://www.kolshop.com.tw/order_create/".$Data["TradeID"];
                //不使用的付款方式
                $input["IgnorePayment"] = ECPay_PaymentMethod::GooglePay;
                //Credit信用卡分期付款延伸參數(可依系統需求選擇是否代入)
                //以下參數不可以跟信用卡定期定額參數一起設定
                $input['CreditInstallment'] = '' ;    //分期期數，預設0(不分期)，信用卡分期可用參數為:3,6,12,18,24
                $input['InstallmentAmount'] = 0 ;    //使用刷卡分期的付款金額，預設0(不分期)
                $input['Redeem'] = false ;           //是否使用紅利折抵，預設false
                $input['UnionPay'] = false;          //是否為聯營卡，預設false;
            }

            //
            $action = $this->ECURL."/Cashier/AioCheckOut/V5";

            return $autoSubmitFormService->generate($input, $action);
        } catch (\Exception $e) {
//            return '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
            return $e->getMessage();
        }
    }
    /**物流 列印出貨單**/
    //711
    public function printUNIMART($AllPayLogisticsID, $CVSPaymentNo, $CVSValidationNo)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
                'hashMethod' => 'md5',
            ]);
            $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

            $input = [
                'MerchantID' => $this->MerchantID,
                'AllPayLogisticsID' => $AllPayLogisticsID,
                'CVSPaymentNo' => $CVSPaymentNo,
                'CVSValidationNo' => $CVSValidationNo,
            ];
            $action = $this->ECURL.'/Express/PrintUniMartC2COrderInfo';

            return $autoSubmitFormService->generate($input, $action);
        } catch (RtnException $e) {
//            echo '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
            return $e->getMessage();
        }
    }
    //全家
    public function printFAMI($AllPayLogisticsID, $CVSPaymentNo)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
                'hashMethod' => 'md5',
            ]);
            $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

            $input = [
                'MerchantID' => $this->MerchantID,
                'AllPayLogisticsID' => $AllPayLogisticsID,
                'CVSPaymentNo' => $CVSPaymentNo,
            ];
            $action = $this->ECURL.'/Express/PrintFAMIC2COrderInfo';

            return $autoSubmitFormService->generate($input, $action);
        } catch (RtnException $e) {
//            echo '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
            return $e->getMessage();
        }
    }

    /**
    回傳的綠界科技的付款結果訊息如下:
    Array
    (
    [MerchantID] =>
    [MerchantTradeNo] => 我司 shopID
    [StoreID] =>
    [RtnCode] => 1代表交易成功，其餘都是失敗
    [RtnMsg] => 交易訊息
    [TradeNo] => 綠界交易編號
    [TradeAmt] => 交易金額
    [PaymentDate] => 付款時間
    [PaymentType] => ATM_LAND、ATM_TAISHIN....ATM_銀行代號
    [PaymentTypeChargeFee] => 通路費
    [TradeDate] => 訂單成立時間
    [SimulatePaid] => 1代表為模擬付款 0代表為正常交易付款
    [CustomField1] =>
    [CustomField2] =>
    [CustomField3] =>
    [CustomField4] =>
    [CheckMacValue] => 檢查碼
    )
     */
    /**
    回傳的綠界科技的物流狀態如下: 【產生訂單就會馬上回傳】
    Array
    (
    [AllPayLogisticsID] => 綠界科技的物流交易編號
    [BookingNote] => 托運單號 物流類型為 HOME 才會回傳。
    [CheckMacValue] =>
    [CVSPaymentNo] => 寄貨編號(C2C) 7-ELEVEN、全家，才會回傳。
    [CVSValidationNo] => 驗證碼 (C2C) 7-ELEVEN 才會回傳。
    [GoodsAmount] => 商品金額
    [LogisticsSubType] => 物流子類型 UNIMART
    [LogisticsType] => 物流類型 CVS
    [MerchantID] =>
    [MerchantTradeNo] =>廠商交易編號 shopID
    收件人資料
    [ReceiverAddress] =>
    [ReceiverCellPhone] =>
    [ReceiverEmail] =>
    [ReceiverName] =>
    [ReceiverPhone] =>

    [RtnCode] => 300 訂單處理中 2030 商品已經送達門市...等等
    [RtnMsg] =>
    [UpdateStatusDate] =>物流狀態更新時間 yyyy/MM/dd HH:mm:ss
    )
     */
    public function replyAIO($Data)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
//                'hashMethod' => 'md5',
            ]);
            $checkoutResponse = $this->factory->create(VerifiedArrayResponse::class);
            $ResponseData = $checkoutResponse->get($Data);
            //
            return $ResponseData;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function replyCVS($Data)
    {
        try {
            $this->factory = new Factory([
                'hashKey' => $this->HashKey,
                'hashIv' => $this->HashIV,
                'hashMethod' => 'md5',
            ]);
            $checkoutResponse = $this->factory->create(VerifiedArrayResponse::class);
            $ResponseData = $checkoutResponse->get($Data);
            return $ResponseData;
        } catch (RtnException $e) {
            echo '(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL;
        }
    }
}
