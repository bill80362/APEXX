<?php
/**
 * https://github.com/PHPMailer/PHPMailer
 **/
namespace App\Libraries\Tools;

class Mail
{
    public $GmailUsername;
    public $GmailPassword;

    public $SendFromMail;
    public $SendFromName;

    public $ErrorMsg = "";

    public function __construct()
    {
        //SMTP
        $this->GmailUsername = $_ENV["Gmail.Username"]; //SMTP伺服器 Gmail帳號
        $this->GmailPassword = $_ENV["Gmail.Password"]; //SMTP伺服器 Gmail密碼
        //寄件人
        $this->SendFromMail = $this->GmailUsername; //寄件人郵件
        $this->SendFromName = $_ENV["Gmail.SendFromName"]; //寄件人名稱
    }
    public function send($toMail, $Subject, $Body, $ShowError = false)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            //是否開啟錯誤顯示
            if ($ShowError) {
                $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            }

            $mail->CharSet = \PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
            $mail->isSMTP();
            // TLS
            $mail->Host        = 'smtp.gmail.com';
            $mail->Username    = $this->GmailUsername;
            $mail->Password    = $this->GmailPassword;
            $mail->SMTPAuth    = true;
            $mail->SMTPSecure  = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAutoTLS = true;
            $mail->Port        = 587;
            //Recipients
            $mail->setFrom($this->SendFromMail, $this->SendFromName);
            $mail->addAddress($toMail, $toMail);
            //Content
            $mail->isHTML(true);
            $mail->Subject = $Subject;
            $mail->Body    = $Body;
            $mail->AltBody = $Body;
            //5秒回覆限制
            $mail->Timeout = 20;
            $mail->send();
        } catch (\Exception$e) {
            $this->ErrorMsg = $e->getMessage();
            return false;
        }
        return true;
    }
    //訂單出貨通知
    public function notifyShipping($TradeID)
    {
        //訂單 母單
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->select("Trade.*,Shipping.QueryLink");
        $oTrade->select("Payment.Title AS PaymentTitle");
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $oTrade->join("Shipping", "Shipping.ShippingID=Trade.ShippingID");
        $TradeData = $oTrade->find($TradeID);
        //訂單 子單
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->select("SubTrade.*");
        $oSubTrade->select("Color.ColorTitle");
        $oSubTrade->select("Size.SizeTitle");
        $oSubTrade->select("Goods.Title AS GoodsTitle");
        $oSubTrade->join("Color", "Color.ColorID=SubTrade.ColorID");
        $oSubTrade->join("Size", "Size.SizeID=SubTrade.SizeID");
        $oSubTrade->join("Goods", "Goods.GoodsID=SubTrade.GoodsID");
        $oSubTrade->where("TradeID", $TradeID);
        $SubTradeList = $oSubTrade->findAll();
        //製作樣板
        $ViewData = [
            'SendDate'     => date("Y-m-d"),
            "TradeData"    => $TradeData,
            "SubTradeList" => $SubTradeList,
        ];
        $HTMLCode = view('/Mail/AfterShip', $ViewData);
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $oMail->send($TradeData["ReceiverEmail"], "出貨通知", $HTMLCode); //給客戶
        //
        return true;
    }
    //付款成功
    public function notifyPaySuccess($TradeID)
    {
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->select("Trade.*");
        $oTrade->select("Payment.Title AS PaymentTitle");
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $TradeData = $oTrade->find($TradeID);
        //訂單 子單
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->select("SubTrade.*");
        $oSubTrade->select("Color.ColorTitle");
        $oSubTrade->select("Size.SizeTitle");
        $oSubTrade->select("Goods.Title AS GoodsTitle");
        $oSubTrade->join("Color", "Color.ColorID=SubTrade.ColorID");
        $oSubTrade->join("Size", "Size.SizeID=SubTrade.SizeID");
        $oSubTrade->join("Goods", "Goods.GoodsID=SubTrade.GoodsID");
        $oSubTrade->where("TradeID", $TradeID);
        $SubTradeList = $oSubTrade->findAll();
        //製作樣板
        $ViewData = [
            'SendDate'     => date("Y-m-d"),
            "TradeData"    => $TradeData,
            "SubTradeList" => $SubTradeList,
        ];
        $AfterPayHTML    = view('/Mail/AfterPay', $ViewData); //客戶
        $AfterPayHTML_us = view('/Mail/AfterPay_us', $ViewData); //廠商
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $oMail->send($TradeData["ReceiverEmail"], "付款成功通知", $AfterPayHTML); //給客戶
        $oMail->send($oMail->GmailUsername, "付款成功通知", $AfterPayHTML_us); //給廠商
        //
        return true;
    }
    //訂單成立
    public function notifyTradeSuccess($TradeID)
    {
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->select("Trade.*");
        $oTrade->select("Payment.Title AS PaymentTitle");
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $TradeData = $oTrade->find($TradeID);
        //訂單 子單
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->select("SubTrade.*");
        $oSubTrade->select("Color.ColorTitle");
        $oSubTrade->select("Size.SizeTitle");
        $oSubTrade->select("Goods.Title AS GoodsTitle");
        $oSubTrade->join("Color", "Color.ColorID=SubTrade.ColorID");
        $oSubTrade->join("Size", "Size.SizeID=SubTrade.SizeID");
        $oSubTrade->join("Goods", "Goods.GoodsID=SubTrade.GoodsID");
        $oSubTrade->where("TradeID", $TradeID);
        $SubTradeList = $oSubTrade->findAll();
        //製作樣板
        $ViewData = [
            'SendDate'     => date("Y-m-d"),
            "TradeData"    => $TradeData,
            "SubTradeList" => $SubTradeList,
        ];
        $HTMLCode = view('/Mail/AfterSendOrder', $ViewData); //客戶
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $oMail->send($TradeData["ReceiverEmail"], "訂單建立成功", $HTMLCode); //給客戶
        //
        return true;
    }
    //訂單未付款通知
    public function notifyTradeWaitPay($TradeID)
    {
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->select("Trade.*");
        $oTrade->select("Payment.Title AS PaymentTitle");
        $oTrade->join("Payment", "Payment.PaymentID=Trade.PaymentID");
        $TradeData = $oTrade->find($TradeID);
        //訂單 子單
//        $oSubTrade = new \App\Models\Trade\SubTrade();
//        $oSubTrade->select("SubTrade.*");
//        $oSubTrade->select("Color.ColorTitle");
//        $oSubTrade->select("Size.SizeTitle");
//        $oSubTrade->select("Goods.Title AS GoodsTitle");
//        $oSubTrade->join("Color","Color.ColorID=SubTrade.ColorID");
//        $oSubTrade->join("Size","Size.SizeID=SubTrade.SizeID");
//        $oSubTrade->join("Goods","Goods.GoodsID=SubTrade.GoodsID");
//        $oSubTrade->where("TradeID",$TradeID);
//        $SubTradeList = $oSubTrade->findAll();
        //製作樣板
        $ViewData = [
            'SendDate'  => date("Y-m-d"),
            "TradeData" => $TradeData,
//            "SubTradeList"=>$SubTradeList,
        ];
        $HTMLCode = view('/Mail/ShopCartNotice', $ViewData); //客戶
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $oMail->send($TradeData["ReceiverEmail"], "您的購物車尚有商品未訂購完成", $HTMLCode); //給客戶
        //
        return true;
    }
}
