<?php

namespace App\Models\Trade;

use CodeIgniter\Model;

class Trade extends Model
{
    protected $table      = 'Trade';
    protected $primaryKey = 'TradeID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "Status" => "in_list[W,P,T,S,A,F,C]",
        "PaymentID" => "required|numeric",
        "PaymentSubtotalFee" => "required|numeric",
        "ShippingID" => "required|numeric",
        "ShippingFree" => "in_list[Y,N]",
        "ShippingFee" => "required|numeric",
        "CouponID" => "required|numeric",
        "DiscountID_Ｇ" => "required|numeric",
        "DiscountID_D" => "required|numeric",
        "Price" => "required|numeric",
        "ReceiverName" => "required",
        "ReceiverPhone" => "required",
        "ReceiverEmail" => "required|valid_email",
        "ReceiverAddressCode" => "required|numeric",
        "ReceiverAddress" => "required",
        "ReceiverMemo" => "permit_empty|max_length[400]",
        //
        "ShippingTime"=>"permit_empty|valid_date[Y-m-d H:i:s]",
        "AdminMemo" => "permit_empty|max_length[400]",
    ];
    protected $validationMessages = [
        "Status" => [
            "in_list" => "狀態項目有[W等待回應,P已付款,T理貨中,S已出貨,A已到貨,F訂單完成,C已取消]",
        ],
        //
        "ShippingTime" => [
            "valid_date" => "出貨時間格式為2011-11-11 12:12:12",
        ],
    ];
    protected $skipValidation     = false;

    public static $Status = [
        "W"=>"等待回應",
        "P"=>"已付款",
        "T"=>"理貨中",
        "S"=>"已出貨",
        "A"=>"已到貨",
        "F"=>"訂單完成",
        "C"=>"已取消",
    ];
    public $ErrorMessage = "";
    //取消訂單、退回庫存
    public function cancelAndStockBack($TradeID)
    {
        $this->resetQuery();
        //檢查是否為C
        $TradeData = $this->find($TradeID);
        if (!$TradeData) {
            $this->ErrorMessage="訂單編號有誤";
            return false;
        }
        if ($TradeData["Status"]=="C") {
            $this->ErrorMessage="訂單已取消，不能重複取消";
            return false;
        }
        //取消訂單
        $this->protect(false);
        $this->update($TradeID, [
            "Status"=>"C",
        ]);
        $this->protect(true);
        //取消底下子單
        $oSubTrade = new \App\Models\Trade\SubTrade();
        $oSubTrade->where("TradeID", $TradeID);
        $SubTradeList = $oSubTrade->findAll();
        if ($SubTradeList) {
            $SubTradeIDList = array_column($SubTradeList, "SubTradeID");
            $oSubTrade->cancelAndStockBack($SubTradeIDList);
        }
        //
        return true;
    }
}
