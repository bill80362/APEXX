<?php

namespace App\Controllers\Admin\Goods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Picture extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/picture";
    public function getList($GoodsID)
    {
        //
        $oPicture = new \App\Models\Goods\GoodsPicture();
        $oPicture->where("GoodsPicture.GoodsID", $GoodsID);
        $oPicture->orderBy("Seq", "ASC");
        $List = $oPicture->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create($GoodsID, $ColorID, $SizeID)
    {
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
        //
        $Data = [];
        $oPicture = new \App\Models\Goods\GoodsPicture();
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
                    "GoodsID"=>$GoodsID,
                    "ColorID"=>$ColorID,
                    "SizeID"=>$SizeID,
                    "Image"=>$this->ImageDirPath."/".$name,
                    "Seq"=>$Seq,
                ]);
                if ($oPicture->errors()) {
                    $ErrorMsg = implode(",", $oPicture->errors());
                }
                //
                $Data[] = [
                    "GoodsPictureID"=>$ID,
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
        $oPicture = new \App\Models\Goods\GoodsPicture();
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
        $oGoodsPicture = new \App\Models\Goods\GoodsPicture();
        $oGoodsPicture->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oGoodsPicture->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
