<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Spec extends BaseController
{
    use ResponseTrait;
    public function getList($SpecCategoryID = -1)
    {
        //
        $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
        if (isset($SpecCategoryID) && $SpecCategoryID != -1) {
            $oSpec->where("SpecCategoryID", $SpecCategoryID);
        } else {
            $oSpec->orderBy("SpecCategoryID");
        }
        $oSpec->orderBy("Seq");
        $oSpec->orderBy("CustomSpecID");
        $List = $oSpec->findAll();

        //關聯客製規格圖片
        $SpecPictureKeyValue = [];
        if (count($List)) {
            $CustomSpecIDArray = array_column($List, "CustomSpecID");
            $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
            $oPicture->whereIn("CustomSpecID", $CustomSpecIDArray);
            $oPicture->orderBy("Seq");
            $oPicture->orderBy("SpecPictureID");
            $Temp = $oPicture->findAll();
            if (count($Temp) > 0) {
                $SpecPictureKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "CustomSpecID");
            }
        }

        //放入資料
        foreach ($List as $key => $Data) {
            $List[$key]["SpecPictureList"] = $SpecPictureKeyValue[$List[$key]["CustomSpecID"]] ?? [];
        }

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
        //檢查ID是否已使用在訂單資料(SubTrade)中
        $db = \Config\Database::connect();
        $sql = "SELECT SubTradeID FROM `SubTrade` WHERE CONCAT(',',`CustomSpecID`,',') LIKE '%," . $ID . ",%'";
        $query = $db->query($sql);
        $SubTradeList = $query->getResultArray();
        if (count($SubTradeList)) {
            return $this->respond(ResponseData::fail("此規格資料已用於訂單之中，無法刪除!"));
        }
        //開始刪除
        $oSpec->protect(false);
        $oSpec->delete($ID);
        if ($oSpec->errors()) {
            $ErrorMsg = implode(",", $oSpec->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //開始刪除關聯資料
        //刪除相關的"客製化商品規格組合異動價"資料
        $sql = "DELETE FROM `CustomGoodsChangePrice` WHERE CONCAT(',',`CustomSpecID`,',') LIKE '%," . $ID . ",%'";
        $db->simpleQuery($sql);
        //刪除相關的"客製化商品規格黑名單"資料
        $sql = "DELETE FROM `CustomGoodsSpecBlacklist` WHERE CONCAT(',',`CustomSpecID`,',') LIKE '%," . $ID . ",%'";
        $db->simpleQuery($sql);

        $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
        $oPicture->where("CustomSpecID", $ID);
        $PictureList = $oPicture->findAll();
        if (count($PictureList)) {
            //刪除客製規格下的圖檔 & 紀錄
            foreach ($PictureList as $Data) {
                if (isset($Data["Image"]) && $Data["Image"] != "") {
                    $FileHostPath = ROOTPATH . "public" . $Data["Image"];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
            }
            $SpecPictureIDArray = array_column($PictureList, "SpecPictureID");
            $oPicture->resetQuery();
            $oPicture->protect(false);
            $oPicture->delete($SpecPictureIDArray);
            if ($oPicture->errors()) {
                $ErrorMsg = implode(",", $oPicture->errors());
                return $this->respond(ResponseData::fail($ErrorMsg));
            }
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
