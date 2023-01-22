<?php
namespace App\Libraries\ECPay;

//載入SDK(路徑可依系統規劃自行調整)
require(APPPATH.'/ThirdParty/ECPay/ECPay.Payment.Integration.php');
require(APPPATH.'/ThirdParty/ECPay/Ecpay.Logistic.Integration.php');
//require(APPPATH.'/ThirdParty/ECPay/ECPay.Logistics.Integration.php');

class CheckoutPayment
{
    public $CheckOutString = "";//產生的HTML code
    public $oECPay;//EC物件
    //EC Test
    public $MerchantID = "2000132";
    public $HashKey= "5294y06JbISpM5x9";
    public $HashIV= "v77hoKGq4kWxNNIS";
    public $ECURL = "https://payment-stage.ecpay.com.tw";
    //客戶正式用
//    public $MerchantID = "3112983";
//    public $HashKey= "VcUq8LBlIOZ1dCtO";
//    public $HashIV= "LxIdd1XklmpVTkZV";
//    public $ECURL = "https://payment.ecpay.com.tw";

    public $HOME_URL = "";
    public $SERVER_URL = "";

    public function __construct($Data=""){
        //
        $this->HOME_URL = base_url();
        $this->SERVER_URL = base_url();

        //參數修改
        if($Data=="CVS"){
            $this->oECPay = new EcpayLogistics();
            $this->oECPay->HashKey     = $this->HashKey ;                                     //測試用Hashkey，請自行帶入ECPay提供的HashKey
            $this->oECPay->HashIV      = $this->HashIV ;
        }else{
            $this->oECPay = new ECPay_AllInOne();
            $this->oECPay->ServiceURL  = $this->ECURL; //服務位置
            $this->oECPay->HashKey     = $this->HashKey ;                                            //測試用Hashkey，請自行帶入ECPay提供的HashKey
            $this->oECPay->HashIV      = $this->HashIV ;                                            //測試用HashIV，請自行帶入ECPay提供的HashIV
            $this->oECPay->MerchantID  = $this->MerchantID;                                                      //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $this->oECPay->EncryptType = '1';
        }
    }
    //信用卡
    public function credit($Data){
        $this->oECPay->ServiceURL = $this->oECPay->ServiceURL."/Cashier/AioCheckOut/V5";
        //基本參數(請依系統規劃自行調整)
//        $this->oECPay->Send['ReturnURL']         = $this->HOME_URL.'/tightpac_website/Order/'.$Data["ShopID"] ;    //付款完成通知回傳的網址
        $this->oECPay->Send['ReturnURL']         = $this->SERVER_URL . '/api/ServerReply' ;
        $this->oECPay->Send['MerchantTradeNo']   = $Data["ShopID"];                          //訂單編號
        $this->oECPay->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');                       //交易時間
        $this->oECPay->Send['TotalAmount']       = $Data["DiscountTotal"];                                      //交易金額
        $this->oECPay->Send['TradeDesc']         = "這是交易描述" ;                          //交易描述
        $this->oECPay->Send['ChoosePayment']     = ECPay_PaymentMethod::Credit ;              //付款方式:Credit
        $this->oECPay->Send['IgnorePayment']     = ECPay_PaymentMethod::GooglePay ;           //不使用付款方式:GooglePay

        //導回前端的網址
        $this->oECPay->Send['ClientBackURL']         = $this->HOME_URL . '/' ;
//        $this->oECPay->Send['OrderResultURL']         = $this->HOME_URL . '/Order/?shop_id='.$Data["ShopID"] ;

        //訂單的商品資料
        foreach ($Data["Product"] as $key=>$value){
            array_push($this->oECPay->Send['Items'],
                array(
                    'Name' => $value["Title"],
                    'Price' => (int)$value["Price"],
                    'Currency' => "元",
                    'Quantity' => (int)$value["Count"],
                    'URL' => "url"
                )
            );
        }

        //Credit信用卡分期付款延伸參數(可依系統需求選擇是否代入)
        //以下參數不可以跟信用卡定期定額參數一起設定
        $this->oECPay->SendExtend['CreditInstallment'] = '' ;    //分期期數，預設0(不分期)，信用卡分期可用參數為:3,6,12,18,24
        $this->oECPay->SendExtend['InstallmentAmount'] = 0 ;    //使用刷卡分期的付款金額，預設0(不分期)
        $this->oECPay->SendExtend['Redeem'] = false ;           //是否使用紅利折抵，預設false
        $this->oECPay->SendExtend['UnionPay'] = false;          //是否為聯營卡，預設false;

        //產生訂單(auto submit至ECPay)
        $this->CheckOutString =  $this->oECPay->CheckOutString();
    }
    //ATM
    public function atm($Data){
        $this->oECPay->ServiceURL = $this->oECPay->ServiceURL."/Cashier/AioCheckOut/V5";
        //基本參數(請依系統規劃自行調整)
//        $this->oECPay->Send['ReturnURL']         = $this->HOME_URL.'/tightpac_website/Order/'.$Data["ShopID"] ;    //付款完成通知回傳的網址
        $this->oECPay->Send['ReturnURL']         = $this->SERVER_URL . '/api/ServerReply' ;
        $this->oECPay->Send['MerchantTradeNo']   = $Data["ShopID"];                          //訂單編號
        $this->oECPay->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');                       //交易時間
        $this->oECPay->Send['TotalAmount']       = $Data["DiscountTotal"];                    //交易金額
        $this->oECPay->Send['TradeDesc']         = "這是交易描述" ;                          //交易描述
        $this->oECPay->Send['ChoosePayment']     = ECPay_PaymentMethod::ATM ;                 //付款方式:ATM

        //導回前端的網址
        $this->oECPay->Send['ClientBackURL']         = $this->HOME_URL . '/' ;
        $this->oECPay->Send['OrderResultURL']         = $this->HOME_URL . '/Order/?shop_id='.$Data["ShopID"] ;

        //訂單的商品資料
        foreach ($Data["Product"] as $key=>$value){
            array_push($this->oECPay->Send['Items'],
                array(
                    'Name' => $value["Title"],
                    'Price' => (int)$value["Price"],
                    'Currency' => "元",
                    'Quantity' => (int)$value["Count"],
                    'URL' => "url"
                )
            );
        }

        //ATM 延伸參數(可依系統需求選擇是否代入)
        $this->oECPay->SendExtend['ExpireDate'] = 3 ;     //繳費期限 (預設3天，最長60天，最短1天)
        $this->oECPay->SendExtend['PaymentInfoURL'] = $this->SERVER_URL . '/api/ServerReply' ; //伺服器端回傳付款相關資訊

        //產生訂單(auto submit至ECPay)
        $this->CheckOutString =  $this->oECPay->CheckOutString();
    }
    //執行跳轉
    public function go(){
        $this->oECPay->CheckOut();
    }
    //取得統一超商地點
    public function CvsMap(){

        $this->oECPay->Send = array(
            'MerchantID' => $this->MerchantID,
            'MerchantTradeNo' => 'no' . date('YmdHis'),
            'LogisticsSubType' => EcpayLogisticsSubType::UNIMART_C2C,//UNIMART 統一超商
            'IsCollection' => EcpayIsCollection::YES,//是否代收貨款
//            'ServerReplyURL' => $this->SERVER_URL.'/api/711Map',//選完電子地圖後，導回的URL
//            'ServerReplyURL' => $this->HOME_URL.'/tightpac_website/test.php',
            'ServerReplyURL' => $this->HOME_URL.'/test.php',
            'ExtraData' => '測試額外資訊',//供廠商傳遞保留的資訊，在回傳參數中，會原值回傳。
            'Device' => EcpayDevice::PC //Mobile或PC 7-11有差 全家沒差
        );
        // CvsMap(Button名稱, Form target)
        $html = $this->oECPay->CvsMap('選擇7-11超商');
        //是否自動跳轉
        if(false){$html .= "<script type='text/javascript'>document.getElementById('ECPayForm').submit();</script>";}
        echo $html;
    }
    //送出
    public function goCvs($Data){

        //訂單的商品資料
        $ProductTxt = "";
//        foreach ($Data["Product"] as $key=>$value){
//            $product = array($value["Title"],$value["Price"],(int)$value["Count"]);
//            $ProductTxt .= "@".implode(",",$product);
//        }

        $this->oECPay->Send = array(
            'MerchantID' => $this->MerchantID,
            'MerchantTradeNo' => $Data["ShopID"],
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'LogisticsType' => EcpayLogisticsType::CVS,
            'LogisticsSubType' => EcpayLogisticsSubType::UNIMART_C2C,//UNIMART 統一超商
            'GoodsAmount' => (int)$Data["DiscountTotal"],//商品金額1~20000
            'CollectionAmount' => (int)$Data["DiscountTotal"], //代收金額
            'IsCollection' => EcpayIsCollection::YES,//是否代收貨款
            //寄件人:公司資料
            'GoodsName' => '太配樂密鮮罐',
            'SenderName' => '太配樂',
            'SenderPhone' => '0423328895',
            'SenderCellPhone' => '0958952203',
            //收件人:
            'ReceiverName' => $Data["Name"],
            'ReceiverPhone' => $Data["Phone"],
            'ReceiverCellPhone' => $Data["Phone"],//注意事項:只允許數字、10 碼、09 開頭
            'ReceiverEmail' => $Data["Email"],

            'TradeDesc' => '交易敘述',
            'ServerReplyURL' => $this->SERVER_URL . '/api/ServerReplyLogistics',//物流狀態都會透過此URL通知
//            'ClientReplyURL' => $this->SERVER_URL . '/api/711Map',//按下按鈕過去結帳後回來的路徑，背景作業可以不填
            'ClientReplyURL' => $this->HOME_URL . '/Order/?shop_id='.$Data["ShopID"],//按下按鈕過去結帳後回來的路徑，背景作業可以不填
            'LogisticsC2CReplyURL' => $this->SERVER_URL . '/api/ServerReplyLogisticsStore',//當 User 選擇取貨門市有問題時，會透過此 URL 通知特店，請特店通知 User 重新選擇門市。
            'Remark' => $ProductTxt,//這邊放入產品細目
            'PlatformID' => '',//特約合作平台商代號
        );

        $this->oECPay->SendExtend = array(
            'ReceiverStoreID' => $Data["ECReceiverStoreID"],//收件人門市代號
            'ReturnStoreID' => '',//退貨門市代號
        );
        // CreateShippingOrder()
        $Result = $this->oECPay->CreateShippingOrder();
//        echo '<pre>' . print_r($Result, true) . '</pre>';
        //是否自動跳轉
        if(false){$Result .= "<script type='text/javascript'>document.getElementById('ECPayForm').submit();</script>";}
        echo $Result;
        exit();
    }
    //去EC抓訂單資料
    public function getTradeInfo(){
        //
        $this->oECPay->ServiceURL = $this->oECPay->ServiceURL."/Cashier/QueryTradeInfo/V5";

        //基本參數(請依系統規劃自行調整)
        $this->oECPay->Query['MerchantTradeNo'] = 'Test1588214329';
        $this->oECPay->Query['TimeStamp']       = time() ;

        //查詢訂單
        $info = $this->oECPay->QueryTradeInfo();

        //顯示訂單資訊
        echo "<pre>" . print_r($info, true) . "</pre>";
    }
    //去EC抓訂單資料
    public function getTradeInfo_Logistics(){

        $this->oECPay->Send = array(
            'MerchantID' => $this->MerchantID,
            'AllPayLogisticsID' => '1607810',//綠界物流訂單編號
            'PlatformID' => ''
        );
        // QueryLogisticsInfo()
        $Result = $this->oECPay->QueryLogisticsInfo();
        echo '<pre>' . print_r($Result, true) . '</pre>';
    }
    //去EC抓 託運單
    public function ECBill_UnimartC2C($_AllPayLogisticsID,$_CVSPaymentNo,$_CVSValidationNo){
            $this->oECPay->Send = array(
                'MerchantID' => $this->MerchantID,
                'AllPayLogisticsID' => $_AllPayLogisticsID,
                'CVSPaymentNo' => $_CVSPaymentNo,
                'CVSValidationNo' => $_CVSValidationNo,
                'PlatformID' => ''
            );
            // PrintUnimartC2CBill(Button名稱, Form target)
            $html = $this->oECPay->PrintUnimartC2CBill('列印繳款單(統一超商C2C)');
            return $html;
    }
    //ECReply
    public function ECReply(){
        //DB紀錄
        $oECReply = new ECReply();
        $oECReply->create(array("Data"=> print_r($_POST,true)));
        //檢查回傳
        $this->oECPay->CheckOutFeedback($_POST);
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
        //更新DB
        $ShopID = $_POST["MerchantTradeNo"];
        $Data = array(
            "ECPaymentType"=>$_POST["PaymentType"],
            "ECAmount"=>$_POST["TradeAmt"],
            "ECTradeNo"=>$_POST["TradeNo"],
            "ECRtnCode"=>$_POST["RtnCode"],
            "ECRtnMsg"=>$_POST["RtnMsg"],
        );
        //更新訂單狀態
        if($_POST["PaymentType"]==1){
            $Data["Status"] = "Y";
        }
        if(isset($_POST["BankCode"])){
            $Data["ECData"] = "(".$_POST["BankCode"].")".$_POST["vAccount"]."_Ex:".$_POST["ExpireDate"];
        }
        if(isset($_POST["PaymentTypeChargeFee"])){
            $Data["ECChargeFee"] = $_POST["PaymentTypeChargeFee"];
        }
        if(isset($_POST["PaymentDate"])){
            $Data["ECPaymentDate"] = $_POST["PaymentDate"];
        }
        //SQL
        $oShop = new Shop();
        //訂單已經成功/失敗的不能改
        if($oShop->update($Data,"ShopID='".$ShopID."' AND (Status='W' OR Status='S')  ")){
            //發出訂購單信件
//            $oShop = new Shop();
//            $Shop = $oShop->getData("ShopID='".$ShopID."'");
//            $oSendMail = new SendMail();
//            $oSendMail->sendMailTpl1($Shop);

            //抓出訂單資料
            $oShop = new Shop();
            $ShopData = $oShop->getData("ShopID='".$ShopID."'");
            //寄出ATM虛擬帳號資造
            $oSendMail = new SendMail();
            if($ShopData["Payment"]=="ATM" && $ShopData["ECRtnCode"]==2 && $ShopData["ECData"]!=""){
                //虛擬ATM信件
                $MailBody = MailTemp::toCustomerATM($ShopData);
                $oSendMail->sendMail($ShopData["Email"],"TIGHTPACTW網路商城-ATM繳費帳號",$MailBody);
            }elseif( in_array($ShopData["Payment"],["ATM","CREDIT"]) && $ShopData["ECRtnCode"]==1 ){
                //付款成功 寄信給公司
                $MailBody = MailTemp::toCompany($ShopData,"已下單","已付款");
                $oSendMail->sendMail($oSendMail->SendFromMail,"TIGHTPACTW網路商城-感謝您的訂購",$MailBody);
                //付款成功 寄信給客戶
                $MailBody = MailTemp::toCustomer($ShopData,"已下單","已付款","備貨中");
                $oSendMail->sendMail($ShopData["Email"],"TIGHTPACTW網路商城-感謝您的訂購",$MailBody);
            }


            // 在網頁端回應 1|OK
            echo '1|OK';
            exit();
        }
        //Error
        echo '0|Error';
        exit();


    }
    //ECReply-以物流狀態進行相對應的處理
    public function ECReply_Logistics(){
        //DB紀錄
        $oECReply = new ECReply();
        $oECReply->create(array("Data"=> print_r($_POST,true)));
        //檢查回傳
        $this->oECPay->CheckOutFeedback($_POST);
        // 以物流狀態進行相對應的處理
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
        //更新DB
        $ShopID = $_POST["MerchantTradeNo"];
        $Data = array(
            "ECPaymentType"=>$_POST["LogisticsType"]."_".$_POST["LogisticsSubType"],
            "ECAmount"=>$_POST["GoodsAmount"],
            "ECTradeNo"=>$_POST["AllPayLogisticsID"],
            "ECRtnCode"=>$_POST["RtnCode"],
            "ECRtnMsg"=>$_POST["RtnMsg"],
        );
        //更新訂單狀態
        //不知道什麼情況算是這筆交易完成
//        if($_POST["PaymentType"]==1){
//            $Data["Status"] = "Y";
//        }
//        if(isset($_POST["ReceiverName"])){
//            $Data["ECData"] = $_POST["ReceiverName"]."_".$_POST["ReceiverCellPhone"]."_".$_POST["ReceiverEmail"];
//        }
//        if(isset($_POST["PaymentTypeChargeFee"])){
//            $Data["ECChargeFee"] = $_POST["PaymentTypeChargeFee"];
//        }
        if(isset($_POST["UpdateStatusDate"])){
            $Data["ECPaymentDate"] = $_POST["UpdateStatusDate"];
        }
        //CVS_UNIMARTC2C才有的參數，之後列印託運單會用到
        if(isset($_POST["CVSPaymentNo"])){
            $Data["ECCVSPaymentNo"] = $_POST["CVSPaymentNo"];
        }
        if(isset($_POST["CVSValidationNo"])){
            $Data["ECCVSValidationNo"] = $_POST["CVSValidationNo"];
        }
        //SQL
        $oShop = new Shop();
        //訂單已經成功/失敗的不能改
        if($oShop->update($Data,"ShopID='".$ShopID."' AND (Status='W' OR Status='S') ")){
            //發出訂購單信件
//            $oShop = new Shop();
//            $Shop = $oShop->getData("ShopID='".$ShopID."'");
//            $oSendMail = new SendMail();
//            $oSendMail->sendMailTpl1($Shop);
            //抓出訂單資料
            $oShop = new Shop();
            $ShopData = $oShop->getData("ShopID='".$ShopID."'");
            //寄出ATM虛擬帳號資造
            $oSendMail = new SendMail();
            if($ShopData["Payment"]=="CVS" && $ShopData["ECRtnCode"]==300 && $ShopData["ECData"]!=""){
                //付款成功 寄信給公司
                $MailBody = MailTemp::toCompany($ShopData,"已下單","未付款");
                $oSendMail->sendMail($oSendMail->SendFromMail,"TIGHTPACTW網路商城-訂購成功通知信件",$MailBody);
                //付款成功 寄信給客戶
                $MailBody = MailTemp::toCustomer($ShopData,"已下單","未付款","備貨中");
                $oSendMail->sendMail($ShopData["Email"],"TIGHTPACTW網路商城-訂購成功通知信件",$MailBody);
            }


            // 在網頁端回應 1|OK
            echo '1|OK';
            exit();
        }
        //Error
        echo '0|Error';
        exit();
    }
    //ECReply-物流以更新門市通知進行相對應的處理
    public function ECReply_Logistics_Store(){
        //DB紀錄
        $oECReply = new ECReply();
        $oECReply->create(array("Data"=> print_r($_POST,true)));
        //檢查回傳
        $this->oECPay->CheckOutFeedback($_POST);
        /**
        回傳的綠界科技的更新門市通知如下:
        Array
        (
        [AllPayLogisticsID] => 綠界科技的物流交易編號
        [CheckMacValue] =>
        [GoodsAmount] =>商品金額
        [GoodsName] =>物品名稱
        [MerchantID] =>
        [Status] =>狀態代碼
         *  01：門市關轉店
            02：門市舊店號更新(同樣一間門市，但是更換店號)
            03：退件門市為原寄件門市，但無寄件門市資料
            04：取(退)件門市臨時關轉店
        [StoreID] =>門市店號
        [StoreType] =>01：取件門市更新 02：退件門市更新

        )
         */
        //DB
        $oECReply_Logistics_Store = new ECReply_Logistics_Store();
        if($oECReply_Logistics_Store->create($_POST)){
            // 在網頁端回應 1|OK
            echo '1|OK';
            exit();
        }
    }

}