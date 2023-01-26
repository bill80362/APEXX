<?php

namespace App\Controllers;

class JKOReply extends BaseController
{
    public function reply()
    {
        //Log
        $oReplyLog = new \App\Models\ReplyLog\ReplyLog();
        $oReplyLog->protect(false);
        $oReplyLog->insert([
            "DataType" => "JK",
            "PostData"=>print_r($this->request->getJSON(true), true),
        ]);
        //
        $JsonVar = $this->request->getJSON(true);
        if (!$JsonVar) {
            return false;
        }
        //
        $ThirdPartyID = $JsonVar["transaction"]["platform_order_id"];
        $StatusCode = $JsonVar["transaction"]["status"];
        //抓訂單資訊
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");
        $oTrade->where("ThirdPartyID", $ThirdPartyID);
        $Data = $oTrade->first();
        if (!$Data) {
            return false;
        }
        // 0 代表交易成功
        if ($StatusCode==0) {
            //更新訂單為付款成功
            $oTrade = new \App\Models\Trade\Trade();
            $oTrade->protect(false);
            $oTrade->update($Data["TradeID"], [
                "Status"=>"P",//已付款
                "PaymentTime"=>date("Y-m-d H:i:s"),//付款時間
            ]);
        }
        echo "OK";
    }
}
