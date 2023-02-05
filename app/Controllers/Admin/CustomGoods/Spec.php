<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Spec extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        $oSpec->orderBy("Seq");
        $oSpec->orderBy("CustomSpecID", "DESC");
        $List = $oSpec->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $SpecCategoryID = $this->request->getVar("SpecCategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //檢查ID
        if ($SpecCategoryID) {
            $oCategory = new \App\Models\CustomGoods\CustomGoodsSpecCategory();
            $CategoryData = $oCategory->find($SpecCategoryID);
            if (!$CategoryData) {
                return $this->respond(ResponseData::fail("找不到對應的客製規格類別ID"));
            }
        }
        //
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        $oSpec->protect(false);
        $InsertID = $oSpec->insert([
           "SpecCategoryID"=>$SpecCategoryID,
           "Title"=>$Title,
           "Seq"=>$Seq,
           "Status"=>$Status,
        ]);
        if ($oSpec->errors()) {
            $ErrorMsg = implode(",", $oSpec->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "CustomSpecID"=>$InsertID,
            "SpecCategoryID"=>$SpecCategoryID,
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
        $SpecCategoryID = $this->request->getVar("SpecCategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        //檢查ID
        $Data = $oSpec->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oSpec->protect(false);
        $oSpec->update($ID, [
            "SpecCategoryID"=>$SpecCategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ]);
        if ($oSpec->errors()) {
            $ErrorMsg = implode(",", $oSpec->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "CustomSpecID"=>$ID,
            "SpecCategoryID"=>$SpecCategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        //檢查ID
        $Data = $oSpec->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oSpec->protect(false);
        $oSpec->delete($ID);
        if ($oSpec->errors()) {
            $ErrorMsg = implode(",", $oSpec->errors());
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
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        $oSpec->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oSpec->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
