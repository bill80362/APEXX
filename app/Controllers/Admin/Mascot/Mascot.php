<?php

namespace App\Controllers\Admin\Mascot;

use App\Controllers\BaseController;
use App\Libraries\ImageBase64;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Mascot extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/mascot";
    public function getList()
    {
        //
        $oMascot = new \App\Models\Mascot\Mascot();
        $oMascot->orderBy("Seq");
        $oMascot->orderBy("MascotID", "DESC");
        $List = $oMascot->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        //
        $oMascot = new \App\Models\Mascot\Mascot();
        $oMascot->protect(false);
        $MascotID = $oMascot->insert([
            "Image1"=>$Image1,
            "Image2"=>$Image2,
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
        ]);
        if ($oMascot->errors()) {
            $ErrorMsg = implode(",", $oMascot->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oMascot->find($MascotID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        //
        $oMascot = new \App\Models\Mascot\Mascot();
        //檢查ID
        $Data = $oMascot->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oMascot->protect(false);
        $updateData = [
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
        ];
        if ($Image1!==null) {
            //刪除原本圖檔
            if (isset($Data["Image1"]) && $Data["Image1"]!="") {
                $FileHostPath = ROOTPATH."public".$Data["Image1"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //更新路徑
            $updateData["Image1"] = $Image1;
        }
        if ($Image2!==null) {
            //刪除原本圖檔
            if (isset($Data["Image2"]) && $Data["Image2"]!="") {
                $FileHostPath = ROOTPATH."public".$Data["Image2"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //更新路徑
            $updateData["Image2"] = $Image2;
        }
        $oMascot->update($ID, $updateData);
        if ($oMascot->errors()) {
            $ErrorMsg = implode(",", $oMascot->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oMascot->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oMascot = new \App\Models\Mascot\Mascot();
        //檢查ID
        $Data = $oMascot->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //刪除原本圖檔
        if (isset($Data["Image1"]) && $Data["Image1"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image1"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除原本圖檔
        if (isset($Data["Image2"]) && $Data["Image2"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image2"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除DB
        $oMascot->protect(false);
        $oMascot->delete($ID);
        if ($oMascot->errors()) {
            $ErrorMsg = implode(",", $oMascot->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oMascot = new \App\Models\Mascot\Mascot();
        //檢查ID
        $Data = $oMascot->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //上傳圖片 Image 多張
        for ($i=1;$i<=2;$i++) {
            $file = $this->request->getFile('Image'.$i);
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb')>5) {
                    return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                }
                if (!in_array($file->getMimeType(), ["image/jpg","image/png","image/gif","image/jpeg","image/webp"], true)) {
                    return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));
                }                //刪除原本圖片
                if (isset($Data["Image".$i]) && $Data["Image".$i]!="") {
                    $FileHostPath = ROOTPATH."public".$Data["Image".$i];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //更新DB
                $oMascot->resetQuery();
                $oMascot->protect(false);
                $oMascot->update($ID, ["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oMascot->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oMascot = new \App\Models\Mascot\Mascot();
        $oMascot->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oMascot->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
