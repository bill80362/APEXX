<?php

namespace App\Libraries;

//取消逾期的等待訂單
class MailWaitTrade
{
    public $Day = 2;//幾天前的訂單

    public function mail()
    {
        //抓出要取消的訂單
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");//只有Ｗ狀態能夠補上付款
        $oTrade->where("created_at <=", date("Y-m-d 23:59:59"), strtotime("-".$this->Day." day"));
        $List = $oTrade->findAll();
        //
        foreach ($List as $TradeData) {
            /**訂單未繳費通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $oLibMail->notifyTradeWaitPay($TradeData["TradeID"]);
        }
    }
}
