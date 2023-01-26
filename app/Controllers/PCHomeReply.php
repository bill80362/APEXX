<?php

namespace App\Controllers;

use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class PCHomeReply extends BaseController
{
    use ResponseTrait;
    public function reply($ID)
    {
        //Log
        $oNotifyReply = new \App\Models\ReplyLog\ReplyLog();
        $oNotifyReply->protect(false);
        $oNotifyReply->insert([
            "DataType" => "PC",
            "PostData"=>print_r($this->request->getPost(), true),
        ]);
        //
        $PostData = $this->request->getPost();
        if (!$PostData) {
            return false;
        }
        //通知類型分類
        if ($PostData["notify_type"]=="order_confirm") {
            $PostData["notify_message"] = json_decode($PostData["notify_message"], true);
            //訂單已成立 S 使用者已繳款
            $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
            $oPCHomePay->updateTrade($PostData["notify_message"]["order_id"]);
        } elseif ($PostData["notify_type"]=="order_audit") {
            $PostData["notify_message"] = json_decode($PostData["notify_message"], true);
            //訂單交易等待中 W ATM等待繳款中
            $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
            $oPCHomePay->updateTrade($PostData["notify_message"]["order_id"]);
        } elseif ($PostData["notify_type"]=="order_expired" || $PostData["notify_type"]=="order_failed") {
            $PostData["notify_message"] = json_decode($PostData["notify_message"], true);
            //訂單已失敗 F
            $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
            $oPCHomePay->updateTrade($PostData["notify_message"]["order_id"]);
        } elseif ($PostData["notify_type"]=="refund_pending") {
            $PostData["notify_message"] = json_decode($PostData["notify_message"], true);
            //未核帳待處理P ATM等待繳款中、信用卡訂單審單中
            $oPCHomePay = new \App\Libraries\Payment\PCHomePay();
            $oPCHomePay->updateTrade($PostData["notify_message"]["order_id"]);
        } elseif ($PostData["notify_type"]=="refund_success") {
            //退款完成 S
        } elseif ($PostData["notify_type"]=="refund_fail") {
            //退款完成 S
        }
        //
        echo "success";
    }
}
