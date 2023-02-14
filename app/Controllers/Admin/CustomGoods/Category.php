<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Category extends BaseController
{
    use ResponseTrait;
    public function getList($GoodsID = -1)
    {
        //
        $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
        if (isset($GoodsID) && $GoodsID != -1) {
            $oCategory->where("GoodsID", $GoodsID);
        } else {
            $oCategory->orderBy("GoodsID");
        }
        $oCategory->orderBy("Seq");
        $oCategory->orderBy("SpecCategoryID");
        $List = $oCategory->findAll();

        //關聯客製規格資料
        $SpecCategoryKeyValue = [];
        if (count($List)) {
            $SpecCategoryIDArray = array_column($List, "SpecCategoryID");
            $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            $oSpec->whereIn("SpecCategoryID", $SpecCategoryIDArray);
            $oSpec->orderBy("Seq");
            $oSpec->orderBy("CustomSpecID");
            $Temp = $oSpec->findAll();
            if (count($Temp) > 0) {
                $SpecCategoryKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "SpecCategoryID");
            }
        }

        //放入資料
        foreach ($List as $key => $Data) {
            $List[$key]["CustomSpecList"] = $SpecCategoryKeyValue[$List[$key]["SpecCategoryID"]] ?? [];
        }

        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //
        //檢查商品ID
        $oGoods = new \App\Models\Goods\Goods();
        $Data = $oGoods->find($GoodsID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("商品ID有誤"));
        }
        $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
        $oCategory->protect(false);
        $InsertID = $oCategory->insert([
           "GoodsID"=>$GoodsID,
           "Title"=>$Title,
           "Seq"=>$Seq,
           "Status"=>$Status,
        ]);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "SpecCategoryID"=>$InsertID,
            "GoodsID"=>$GoodsID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $GoodsID = $this->request->getVar("GoodsID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //
        //檢查商品ID
        $oGoods = new \App\Models\Goods\Goods();
        $Data = $oGoods->find($GoodsID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("商品ID有誤"));
        }
        //檢查ID
        $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
        $Data = $oCategory->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oCategory->protect(false);
        $oCategory->update($ID, [
            "GoodsID"=>$GoodsID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ]);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "SpecCategoryID"=>$ID,
            "GoodsID"=>$GoodsID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
        //檢查ID
        $Data = $oCategory->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //檢查資料是否已被關聯

        //開始刪除
        $oCategory->protect(false);
        $oCategory->delete($ID);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
        $oCategory->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oCategory->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
