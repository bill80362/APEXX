<?php

namespace App\Controllers;

use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class TradeCancel extends BaseController
{
    public function cancelAndStockBack($TradeID)
    {
        //首頁網址
        $redirectURL = $_ENV["app.frontURL"];
        //訂單取消
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");//只有Ｗ狀態能夠取消
        $Data = $oTrade->find($TradeID);
        //沒有符合的 直接導回
        if (!$Data) {
            return redirect()->to($redirectURL);
        }
        //取消訂單、返回庫存
        $oTrade->resetQuery();
        $oTrade->cancelAndStockBack($Data["TradeID"]);
        //導回前台首頁
        return redirect()->to($redirectURL);
    }
}
