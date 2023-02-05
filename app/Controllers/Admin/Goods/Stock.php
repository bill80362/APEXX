<?php

namespace App\Controllers\Admin\Goods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Stock extends BaseController
{
    use ResponseTrait;
    public function getList($GoodsID)
    {
        //
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oGoodsStock->select("GoodsStock.*");
        $oGoodsStock->select("Goods.Title as GoodsTitle");
        $oGoodsStock->select("Color.ColorTitle");
        $oGoodsStock->select("Size.SizeTitle");
        $oGoodsStock->join("Goods", "Goods.GoodsID=GoodsStock.GoodsID");
        $oGoodsStock->join("Color", "Color.ColorID=GoodsStock.ColorID");
        $oGoodsStock->join("Size", "Size.SizeID=GoodsStock.SizeID");
        $oGoodsStock->where("GoodsStock.GoodsID", $GoodsID);
        $oGoodsStock->groupStart();
        $oGoodsStock->where("Goods.IsCustom", "N");
        $oGoodsStock->orWhere("Goods.IsCustom", "");
        $oGoodsStock->groupEnd();
        $oGoodsStock->orderBy("GoodsStock.Seq", "ASC");
        $List = $oGoodsStock->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $ColorID = $this->request->getVar("ColorID");
        $SizeID = $this->request->getVar("SizeID");
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
        if ($ColorID) {
            $oColor = new \App\Models\Color\Color();
            $ColorData = $oColor->find($ColorID);
            if (!$ColorData) {
                return $this->respond(ResponseData::fail("顏色ID有誤"));
            }
        }
        if ($SizeID) {
            $oSize = new \App\Models\Size\Size();
            $SizeData = $oSize->find($SizeID);
            if (!$SizeData) {
                return $this->respond(ResponseData::fail("尺寸ID有誤"));
            }
        }
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
//        $oGoodsStock->resetQuery();
//        $oGoodsStock->where("GoodsID",$GoodsID);
//        $oGoodsStock->where("ColorID",$ColorID);
//        $oGoodsStock->where("SizeID",$SizeID);
//        $Data = $oGoodsStock->first();
//        if($Data) return $this->respond(ResponseData::fail("庫存已經建立"));
        //
        $oGoodsStock->resetQuery();
        $oGoodsStock->protect(false);
        $oGoodsStock->replace([
            "GoodsID"=>$GoodsID,
            "ColorID"=>$ColorID,
            "SizeID"=>$SizeID,
            "Stock"=>$Stock,
            "Status"=>$Status,
            "DeliverVolume"=>$DeliverVolume,
            "DeliverWeight"=>$DeliverWeight,
            "Price"=>$Price,
            "SellPrice"=>$SellPrice,
            "MemberSellPrice"=>$MemberSellPrice,
            "Seq"=>$Seq,
        ]);
        if ($oGoodsStock->errors()) {
            $ErrorMsg = implode(",", $oGoodsStock->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $oGoodsStock->resetQuery();
        $oGoodsStock->where("GoodsID", $GoodsID);
        $oGoodsStock->where("ColorID", $ColorID);
        $oGoodsStock->where("SizeID", $SizeID);
        $Data = $oGoodsStock->first();
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oGoodsStock->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oGoodsStock->resetQuery();
            $oGoodsStock->where("GoodsID", $Data->GoodsID);
            $oGoodsStock->where("ColorID", $Data->ColorID);
            $oGoodsStock->where("SizeID", $Data->SizeID);
            $oGoodsStock->set(["Seq"=>$Data->Seq]);
            $oGoodsStock->update();
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
