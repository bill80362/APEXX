<?php

namespace App\Controllers\Admin\Trade;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Trade extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //Filter
        $MemberID = $this->request->getGet("MemberID")??"";
        $Status = $this->request->getGet("Status")??"";

        //
        $Page = $this->request->getGet("Page")??1;
        $PageLimit = $this->request->getGet("PageLimit")??30;
        //
        $oTrade = new \App\Models\Trade\Trade();
        //Filter
        if ($MemberID) {
            $oTrade->where("MemberID", $MemberID);
        }
        if ($Status) {
            $oTrade->where("Status", $Status);
        }
        //
        $oTrade->orderBy("TradeID", "DESC");
        $Count = $oTrade->countAllResults(false);
//        $List = $oTrade->paginate($PageLimit,"",$Page);
        $List = $oTrade->findAll();
        //SubTrade
        $TradeIDList = array_column($List, "TradeID");
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->whereIn("TradeID", $TradeIDList);
        $Temp = $oSubTrade->findAll();
        foreach ($Temp as $key=>$Data) {
            //狀態
            $Temp[$key]["StatusTxt"] = \App\Models\Trade\SubTrade::$Status[$Data["Status"]];
        }
        $SubTradeKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "TradeID");
        //
        foreach ($List as $key => $Data) {
            //狀態
            $List[$key]["StatusTxt"] = \App\Models\Trade\Trade::$Status[$Data["Status"]];
            //SubTrade
            $List[$key]["SubTradeList"] = $SubTradeKeyValue[$Data["TradeID"]]??[];
        }

        //Res
        $ResData = [
            "List"=>$List,
            "Count"=>$Count,
        ];
        return $this->respond(ResponseData::success($ResData));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Status = $this->request->getVar("Status");
        $ShippingTime = $this->request->getVar("ShippingTime");
        $ShippingCode = $this->request->getVar("ShippingCode");
        $PaymentTime = $this->request->getVar("PaymentTime");
        $BuyerName = $this->request->getVar("BuyerName");//姓名
        $BuyerPhone = $this->request->getVar("BuyerPhone");//電話
        $ReceiverName = $this->request->getVar("ReceiverName");
        $ReceiverPhone = $this->request->getVar("ReceiverPhone");
        $ReceiverEmail = $this->request->getVar("ReceiverEmail");
        $ReceiverAddressCode = $this->request->getVar("ReceiverAddressCode");
        $ReceiverAddress = $this->request->getVar("ReceiverAddress");
        $ReceiverStoreNo = $this->request->getVar("ReceiverStoreNo");
        $ReceiverStoreInfo = $this->request->getVar("ReceiverStoreInfo");
        $AdminMemo = $this->request->getVar("AdminMemo");
        //
        $oTrade = new \App\Models\Trade\Trade();
        //檢查ID
        $Data = $oTrade->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oTrade->protect(false);
        $updateData = [];
        if ($Status!==null) {
            $updateData["Status"] = $Status;
        }
        //出貨
        if ($ShippingTime!==null) {
            $updateData["ShippingTime"] = $ShippingTime;
        }
        if ($ShippingCode!==null) {
            $updateData["ShippingCode"] = $ShippingCode;
        }
        //金流
        if ($PaymentTime!==null) {
            $updateData["PaymentTime"] = $PaymentTime;
        }
        //購買人資訊
        if($BuyerName!==NULL)
            $updateData["BuyerName"] = $BuyerName;
        if($BuyerPhone!==NULL)
            $updateData["BuyerPhone"] = $BuyerPhone;
        //收件人資訊
        if ($ReceiverName!==null) {
            $updateData["ReceiverName"] = $ReceiverName;
        }
        if ($ReceiverPhone!==null) {
            $updateData["ReceiverPhone"] = $ReceiverPhone;
        }
        if ($ReceiverEmail!==null) {
            $updateData["ReceiverEmail"] = $ReceiverEmail;
        }
        if ($ReceiverAddressCode!==null) {
            $updateData["ReceiverAddressCode"] = $ReceiverAddressCode;
        }
        if ($ReceiverAddress!==null) {
            $updateData["ReceiverAddress"] = $ReceiverAddress;
        }
        if ($ReceiverStoreNo!==null) {
            $updateData["ReceiverStoreNo"] = $ReceiverStoreNo;
        }
        if ($ReceiverStoreInfo!==null) {
            $updateData["ReceiverStoreInfo"] = $ReceiverStoreInfo;
        }
        if ($AdminMemo!==null) {
            $updateData["AdminMemo"] = $AdminMemo;
        }
        //
        if (count($updateData)>0) {
            $oTrade->update($ID, $updateData);
        }
        if ($oTrade->errors()) {
            $ErrorMsg = implode(",", $oTrade->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        $notifyShipping = false;
        if ($Status=="S") {
            /**出貨通知**/
            $oLibMail = new \App\Libraries\Tools\Mail();
            $notifyShipping = $oLibMail->notifyShipping($ID);
        }
        //Res
        $Data = [
            "TradeData" => $oTrade->find($ID),
            "notifyShipping" => $notifyShipping,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    /**新竹物流**/
    public function sendHCT($TradeID)
    {
        //
        $oTrade = new \App\Models\Trade\Trade();
        $Data = $oTrade->find($TradeID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        if ($Data["ShippingID"]!="5") {
            return $this->respond(ResponseData::fail("運送方式不是新竹貨運"));
        }
        if ($Data["Status"]!="T") {
            return $this->respond(ResponseData::fail("只有[T理貨中]可以操作新竹物流接口"));
        }
        //計算總重量
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->selectSum("DeliverWeight", "TotalDeliverWeight");
        $oSubTrade->where("TradeID", $TradeID);
        $Temp = $oSubTrade->first();
        $TotalDeliverWeight = $Temp["TotalDeliverWeight"]??0;
        //傳過去新竹物流
        $oLibHCT = new \App\Libraries\HCT\HCT();
        $HCTList = $oLibHCT->sendData($TradeID, $Data["ReceiverName"], $Data["ReceiverPhone"], $Data["ReceiverAddress"], $Data["Price"], $TotalDeliverWeight);
        $HCTData = $HCTList[0];//都只傳一組，所以只會回傳一組
        //訂單更新新竹物流的資訊
        if ($HCTData["success"]!="Y" && $HCTData["edelno"]) {
            return $this->respond(ResponseData::fail($HCTData["ErrMsg"]));
        }
        $oTrade->resetQuery();
        $oTrade->protect(false);
        $oTrade->update($TradeID, [
            "Status" => "S",//已出貨
            "ShippingTime" => date("Y-m-d H:i:s"),
            "ShippingCode" => $HCTData["edelno"],//新竹貨運編號
        ]);
        /**出貨通知**/
        $oLibMail = new \App\Libraries\Tools\Mail();
        $notifyShipping = $oLibMail->notifyShipping($TradeID);
        //Res
        $Data = [
            "HCTData" => $HCTData,
            "notifyShipping" => $notifyShipping,
        ];
        //Res
        return $this->respond(ResponseData::success($Data));
    }
    //新竹貨運 轉圖檔
    public function getHCTLabelImage()
    {
        $Data = $this->request->getVar("Data");
        $oLibHCT = new \App\Libraries\HCT\HCT();
        $ImageData = $oLibHCT->getLabelImage($Data);
        //Res
        $this->response->setHeader("Content-type", "image/png");
        return $ImageData;
    }
    //新竹貨運 查詢運送狀況
    public function getHCTInfo()
    {
        //
        $Data = $this->request->getVar("Data");
        //
        $oLibHCT = new \App\Libraries\HCT\HCT();
        $ShippingInfo = $oLibHCT->getShippingInfo($Data);
        $ShippingInfo = str_replace(["\r", "\n", "\r\n", "\n\r","  "], '', $ShippingInfo);//去除無用字元
        //Res
        return $this->respond(ResponseData::success($ShippingInfo));
    }
    /**綠界 7-11/全家 出貨單 **/
    public function sendECLogistics($TradeID)
    {
        //
        $oTrade = new \App\Models\Trade\Trade();
        $Data = $oTrade->find($TradeID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        if (!in_array($Data["ShippingID"], [1,2], true)) {
            return $this->respond(ResponseData::fail("運送方式不是全家店到店或7-ELEVEN超商交貨便"));
        }
        if (!in_array($Data["Status"], ["T","S"], true)) {
            return $this->respond(ResponseData::fail("只有[T理貨中]可以操作新竹物流接口"));
        }
        //
        $oLibECPay = new \App\Libraries\Payment\ECPay();
        if ($Data["ShippingID"]==1) {
            //全家
            $ECData = $oLibECPay->printFAMI($Data["ThirdPartyID"], $Data["ThirdPartyData"]);
        } else {
            //7-11
            $Array = explode("_", $Data["ThirdPartyData"]);
            $ECData = $oLibECPay->printUNIMART($Data["ThirdPartyID"], $Array[0], $Array[1]);
        }
        //
        $oTrade->resetQuery();
        $oTrade->protect(false);
        $oTrade->update($TradeID, [
            "Status" => "S",//已出貨
            "ShippingTime" => date("Y-m-d H:i:s"),
            "ShippingCode" => "",//
        ]);
        /**出貨通知**/
        $oLibMail = new \App\Libraries\Tools\Mail();
        $notifyShipping = $oLibMail->notifyShipping($TradeID);
        //Res
        $ResData = [
            "PrintHTML"=> $ECData,
            "notifyShipping"=>$notifyShipping,
        ];
        //Res
        return $this->respond(ResponseData::success($ResData));
    }
}
