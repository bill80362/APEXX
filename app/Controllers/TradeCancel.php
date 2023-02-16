<?php

namespace App\Controllers;

use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class TradeCancel extends BaseController
{
    use ResponseTrait;
    public function cancelAndStockBack($TradeID)
    {
        //訂單取消
        $oTrade = new \App\Models\Trade\Trade();
        $oTrade->where("Status", "W");//只有Ｗ狀態能夠取消
        $Data = $oTrade->find($TradeID);
        //沒有符合的 直接導回
        if (!$Data) {
            return $this->respond(ResponseData::fail("訂單編號錯誤"));
        }
        //取消訂單、返回庫存
        $oTrade->resetQuery();
        $oTrade->cancelAndStockBack($Data["TradeID"]);
        //
        return $this->respond(ResponseData::success([]));
    }
}
