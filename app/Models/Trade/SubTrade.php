<?php

namespace App\Models\Trade;

use CodeIgniter\Model;

class SubTrade extends Model
{
    protected $table      = 'SubTrade';
    protected $primaryKey = 'SubTradeID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public static $Status = [
        "Y"=>"正常",
        "F"=>"退貨",
    ];

    protected $validationRules    = [
        "TradeID" => "required|numeric",
        "Status" => "in_list[Y,F]",
        "GoodsID" => "required|numeric",
        "ColorID" => "required|numeric",
        "SizeID" => "required|numeric",
        "CustomSpecID" => "required",
        "DeliverWeight" => "required|numeric",
        "DeliverVolume" => "required|numeric",
        "DiscountPercentMenu" => "required|numeric",
        "DiscountPercentFull" => "required|numeric",
        "SellPrice" => "required|numeric",
        "FinalPrice" => "required|numeric",
        "RefundPrice" => "required|numeric",
    ];
    protected $validationMessages = [
        "Status" => [
            "in_list" => "狀態項目有[Y正常,F退貨/取消]",
        ],
    ];
    protected $skipValidation     = false;

    //取消子單並退回庫存量
    public function cancelAndStockBack(array $SubTradeIDArray)
    {
        if (count($SubTradeIDArray)==0) {
            return false;
        }
        $this->resetQuery();
        $this->protect(false);
        $this->whereIn("SubTradeID", $SubTradeIDArray);
        $this->where("Status", "Y");//只抓沒退貨的
        $List = $this->findAll();
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oCustomGoodsStock = new \App\Models\CustomGoods\CustomGoodsStock();
        foreach ($List as $Data) {
            $this->resetQuery();
            $this->update($Data["SubTradeID"], [
                "Status"=>"F",//狀態改成已退貨
            ]);
            //返回庫存
            if (isset($Data["CustomSpecID"]) && $Data["CustomSpecID"] != "") {
                $oCustomGoodsStock->resetQuery();
                $oCustomGoodsStock->ioStock($Data["GoodsID"], "1");
            } else {
                $oGoodsStock->resetQuery();
                $oGoodsStock->ioStock($Data["GoodsID"], $Data["ColorID"], $Data["SizeID"], "1");
            }
        }
        $this->protect(true);
        return true;
    }
}
