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
        $oStock->orderBy("CustomGoodsStock.Seq", "ASC");
        $List = $oStock->findAll();

        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();

        foreach ($List as $key=>$Data) {
            // 依據關聯的"客製規格編號"，找出"客製規格"及""客製規格分類"資料
            $CustomSpecIDArray = explode(",", $List[$key]["CustomSpecID"]);
            if (count($CustomSpecIDArray)>0) {
                $oSpec->resetQuery();
                $oSpec->select("CustomGoodsSpec.CustomSpecID");
                $oSpec->select("CustomGoodsSpec.Title AS SpecTitle");
                $oSpec->select("CustomGoodsSpec.Status AS SpecStatus");
                $oSpec->select("CustomGoodsSpec.Seq AS SpecSeq");
                $oSpec->select("CustomGoodsSpec.SpecCategoryID");
                $oSpec->select("CustomGoodsSpecCategory.Title AS SpecCategoryTitle");
                $oSpec->select("CustomGoodsSpecCategory.Status AS SpecCategoryStatus");
                $oSpec->select("CustomGoodsSpecCategory.Seq AS SpecCategorySeq");
                $oSpec->join("CustomGoodsSpecCategory", "CustomGoodsSpecCategory.SpecCategoryID=CustomGoodsSpec.SpecCategoryID");
                $oSpec->whereIn("CustomSpecID", $CustomSpecIDArray);
                $oSpec->orderBy("CustomGoodsSpec.Seq");
                $oSpec->orderBy("CustomGoodsSpecCategory.Seq");
                $oSpec->orderBy("CustomSpecID", "DESC");
                $List[$key]["SpecArray"] = $oSpec->findAll();
            } else {
                $List[$key]["SpecArray"] = [];
            }
        }

        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $CustomSpecID = $this->request->getVar("CustomSpecID");
        $Stock = $this->request->getVar("Stock");
        $Status = $this->request->getVar("Status");
        $DeliverVolume = $this->request->getVar("DeliverVolume");
        $DeliverWeight = $this->request->getVar("DeliverWeight");
        $Price = $this->request->getVar("Price");
        $SellPrice = $this->request->getVar("SellPrice");
        $MemberSellPrice = $this->request->getVar("MemberSellPrice");
        $Seq = $this->request->getVar("Seq");
        //檢查ID
        if ($GoodsID) {
            $oGoods = new \App\Models\Goods\Goods();
            $GoodsData = $oGoods->find($GoodsID);
            if (!$GoodsData) {
                return $this->respond(ResponseData::fail("商品ID有誤"));
            }
        }
        // 檢查每個"客製規格編號"
        if ($CustomSpecID) {
            $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            $CustomSpecIDArray = explode(",", $CustomSpecID);
            foreach ($CustomSpecIDArray as $key=>$Data) {
                $oSpec->resetQuery();
                $SpecData = $oSpec->find($Data);
                if (!$SpecData) {
                    return $this->respond(ResponseData::fail("客製規格編號(CustomSpecID)有誤"));
                }
            }
        }

        $oStock = new \App\Models\CustomGoods\CustomGoodsStock();

        $oStock->resetQuery();
        $oStock->protect(false);
        $oStock->replace([
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
            "Stock"=>$Stock,
            "Status"=>$Status,
            "DeliverVolume"=>$DeliverVolume,
            "DeliverWeight"=>$DeliverWeight,
            "Price"=>$Price,
            "SellPrice"=>$SellPrice,
            "MemberSellPrice"=>$MemberSellPrice,
            "Seq"=>$Seq,
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
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oStock = new \App\Models\CustomGoods\CustomGoodsStock();
        $oStock->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oStock->resetQuery();
            $oStock->where("GoodsID", $Data->GoodsID);
            $oStock->set(["Seq"=>$Data->Seq]);
            $oStock->update();
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
