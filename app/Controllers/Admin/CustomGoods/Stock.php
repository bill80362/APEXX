<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Stock extends BaseController
{
    use ResponseTrait;
    public function getList($GoodsID)
    {
        //
        $oStock = new \App\Models\CustomGoods\CustomGoodsStock();
        $oStock->select("CustomGoodsStock.*");
        $oStock->select("Goods.Title AS GoodsTitle");
        $oStock->join("Goods", "Goods.GoodsID=CustomGoodsStock.GoodsID");
        $oStock->where("CustomGoodsStock.GoodsID", $GoodsID);
        $oStock->where("Goods.IsCustom", "Y");
        $Data = $oStock->first();
        //Res
        return $this->respond(ResponseData::success($Data));
    }
    public function create()
    {
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $Stock = $this->request->getVar("Stock");
        $Status = $this->request->getVar("Status");
        $DeliverVolume = $this->request->getVar("DeliverVolume");
        $DeliverWeight = $this->request->getVar("DeliverWeight");
        $Price = $this->request->getVar("Price");
        $SellPrice = $this->request->getVar("SellPrice");
        $MemberSellPrice = $this->request->getVar("MemberSellPrice");
        //檢查ID
        if ($GoodsID) {
            $oGoods = new \App\Models\Goods\Goods();
            $GoodsData = $oGoods->find($GoodsID);
            if (!$GoodsData) {
                return $this->respond(ResponseData::fail("商品ID有誤"));
            }
        }

        $oStock = new \App\Models\CustomGoods\CustomGoodsStock();

        $oStock->resetQuery();
        $oStock->protect(false);
        $oStock->replace([
            "GoodsID"=>$GoodsID,
            "Stock"=>$Stock,
            "Status"=>$Status,
            "DeliverVolume"=>$DeliverVolume,
            "DeliverWeight"=>$DeliverWeight,
            "Price"=>$Price,
            "SellPrice"=>$SellPrice,
            "MemberSellPrice"=>$MemberSellPrice,
        ]);
        if ($oStock->errors()) {
            $ErrorMsg = implode(",", $oStock->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $oStock->resetQuery();
        $oStock->where("GoodsID", $GoodsID);
        $Data = $oStock->first();
        return $this->respond(ResponseData::success($Data));
    }
}
