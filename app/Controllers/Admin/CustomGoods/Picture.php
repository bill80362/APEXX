<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Picture extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/spec";
    public function getList($CustomSpecID)
    {
        //
        $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
        $oPicture->where("CustomSpecID", $CustomSpecID);
        $oPicture->orderBy("Seq");
        $oPicture->orderBy("SpecPictureID");
        $List = $oPicture->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create($CustomSpecID)
    {
        //檢查ID
        if ($CustomSpecID) {
            $oCustomGoodsSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            $CustomGoodsSpecData = $oCustomGoodsSpec->find($CustomSpecID);
            if (!$CustomGoodsSpecData) {
                return $this->respond(ResponseData::fail("客製規格ID有誤"));
            }
        }
        //
        $Data = [];
        $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
        $oPicture->protect(false);
        for ($i=1;$i<=10;$i++) {
            $file = $this->request->getFile('Image'.$i);
            $Seq = $this->request->getVar('Seq'.$i) ? $this->request->getVar('Seq'.$i) : 1;
            $ErrorMsg = "";
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb')>5) {
                    return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                }
                if (!in_array($file->getMimeType(), ["image/jpg","image/png","image/gif","image/jpeg","image/webp"], true)) {
                    return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));
                }                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //新增DB
                $ID = $oPicture->insert([
                    "CustomSpecID"=>$CustomSpecID,
                    "Image"=>$this->ImageDirPath."/".$name,
                    "Seq"=>$Seq,
                ]);
                if ($oPicture->errors()) {
                    $ErrorMsg = implode(",", $oPicture->errors());
                }
                //
                $Data[] = [
                    "SpecPictureID"=>$ID,
                    "CustomSpecID"=>$CustomSpecID,
                    "Image"=>$this->ImageDirPath."/".$name,
                    "Seq"=>$Seq,
                    "Msg"=>$ErrorMsg,
                ];
            }
        }
        //
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
        //檢查ID
        $Data = $oPicture->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //刪除原本圖檔
        if (isset($Data["Image"]) && $Data["Image"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除DB
        $oPicture->protect(false);
        $oPicture->delete($ID);
        if ($oPicture->errors()) {
            $ErrorMsg = implode(",", $oPicture->errors());
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
        $oPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
        $oPicture->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oPicture->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
