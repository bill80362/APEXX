<?php

namespace App\Controllers\Admin\Video;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Video extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/Video";
    public function getList()
    {
        //
        $oVideo = new \App\Models\Video\Video();
        $oVideo->orderBy("Seq");
        $oVideo->orderBy("VideoID", "DESC");
        $List = $oVideo->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        for ($i=1;$i<=10;$i++) {
            ${'Content'.$i} = $this->request->getVar('Content'.$i);
        }
        $Seq = $this->request->getVar("Seq");
        //檢查目錄ID
        $oCategory = new \App\Models\Video\Category();
        $Data = $oCategory->find($CategoryID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("目錄ID有誤"));
        }
        //
        $oVideo = new \App\Models\Video\Video();
        $oVideo->protect(false);
        $VideoID = $oVideo->insert([
            "VideoCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Content1"=>$Content1,
            "Content2"=>$Content2,
            "Content3"=>$Content3,
            "Content4"=>$Content4,
            "Content5"=>$Content5,
            "Content6"=>$Content6,
            "Content7"=>$Content7,
            "Content8"=>$Content8,
            "Content9"=>$Content9,
            "Content10"=>$Content10,
            "Seq"=>$Seq,
        ]);
        if ($oVideo->errors()) {
            $ErrorMsg = implode(",", $oVideo->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oVideo->find($VideoID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        for ($i=1;$i<=10;$i++) {
            ${'Content'.$i} = $this->request->getVar('Content'.$i);
        }
        $Seq = $this->request->getVar("Seq");
        //
        $oVideo = new \App\Models\Video\Video();
        //檢查ID
        $Data = $oVideo->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oVideo->protect(false);
        $oVideo->update($ID, [
            "Title"=>$Title,
            "Content1"=>$Content1,
            "Content2"=>$Content2,
            "Content3"=>$Content3,
            "Content4"=>$Content4,
            "Content5"=>$Content5,
            "Content6"=>$Content6,
            "Content7"=>$Content7,
            "Content8"=>$Content8,
            "Content9"=>$Content9,
            "Content10"=>$Content10,
            "Seq"=>$Seq,
        ]);
        if ($oVideo->errors()) {
            $ErrorMsg = implode(",", $oVideo->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oVideo->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oVideo = new \App\Models\Video\Video();
        //檢查ID
        $Data = $oVideo->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oVideo->protect(false);
        $oVideo->delete($ID);
        if ($oVideo->errors()) {
            $ErrorMsg = implode(",", $oVideo->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oVideo = new \App\Models\Video\Video();
        //檢查ID
        $Data = $oVideo->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //上傳圖片 Image 多張
        for ($i=1;$i<=1;$i++) {
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
                $oVideo->resetQuery();
                $oVideo->protect(false);
                $oVideo->update($ID, ["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oVideo->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oVideo = new \App\Models\Video\Video();
        $oVideo->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oVideo->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
