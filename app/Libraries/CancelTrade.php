<?php

namespace App\Libraries;

//取消逾期的等待訂單
class CancelTrade
{
    public $Day = 2;//幾天前的訂單

    public function cancelWait(){
        //抓出要取消的訂單
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status","W");//只有Ｗ狀態能夠補上付款
        $oTrade->where("created_at <=",date("Y-m-d 23:59:59"),strtotime("-".$this->Day." day"));
        $List = $oTrade->findAll();
        //ㄊ
        foreach ($List as $Data){
            //取消訂單、返回庫存
            $oTrade->resetQuery();
            $oTrade->cancelAndStockBack($Data["TradeID"]);
        }
    }
}
