<?php

namespace App\Controllers\Admin\News;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class News extends BaseController
{
    public $ImageDirPath = "/image/news";
    use ResponseTrait;
    public function getList(){
        //
        $oNews = new \App\Models\News\News();
        $oNews->orderBy("Seq");
        $oNews->orderBy("NewsID","DESC");
        $List = $oNews->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Content = $this->request->getVar("Content");
        $Image1 = $this->request->getVar("Image1");
        //檢查目錄ID
        $oCategory = new \App\Models\News\Category();
        $Data = $oCategory->find($CategoryID);
        if(!$Data) return $this->respond(ResponseData::fail("目錄ID有誤"));
        //
        $oNews = new \App\Models\News\News();
        $oNews->protect(false);
        $NewsID = $oNews->insert([
            "NewsCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Content"=>$Content,
            "Image1"=>$Image1,
        ]);
        if($oNews->errors()){
            $ErrorMsg = implode(",",$oNews->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oNews->find($NewsID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Content = $this->request->getVar("Content");
        $Image1 = $this->request->getVar("Image1");
        //
        $oNews = new \App\Models\News\News();
        //檢查ID
        $Data = $oNews->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oNews->protect(false);
        $updateData = [
            "NewsCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Content"=>$Content,
        ];
        $oNews->update($ID,$updateData);
        if($Image1!==NULL) {
            //刪除原本圖檔
            if( isset($Data["Image1"]) && $Data["Image1"]!="" ){
                $FileHostPath = ROOTPATH."public".$Data["Image1"];
                if(file_exists($FileHostPath))
                    unlink($FileHostPath);
            }
            //更新路徑
            $updateData["Image1"] = $Image1;
        }
        if($oNews->errors()){
            $ErrorMsg = implode(",",$oNews->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oNews->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function uploadImage($ID){
        $oNews = new \App\Models\News\News();
        //檢查ID
        $Data = $oNews->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //上傳圖片 Image 多張
        for ($i=1;$i<=1;$i++){
            $file = $this->request->getFile('Image'.$i);
            if ( $file && $file->isFile()) {
                if($file->getSizeByUnit('mb')>5) return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                if(!in_array($file->getMimeType(),["image/jpg","image/png","image/gif","image/jpeg","image/webp"])) return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));                //刪除原本圖片
                if( isset($Data["Image".$i]) && $Data["Image".$i]!="" ){
                    $FileHostPath = ROOTPATH."public".$Data["Image".$i];
                    if(file_exists($FileHostPath))
                        unlink($FileHostPath);
                }
                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //更新DB
                $oNews->resetQuery();
                $oNews->protect(false);
                $oNews->update($ID,["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oNews->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //
        $oNews = new \App\Models\News\News();
        //檢查ID
        $Data = $oNews->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始刪除
        $oNews->protect(false);
        $oNews->delete($ID);
        if($oNews->errors()){
            $ErrorMsg = implode(",",$oNews->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oNews = new \App\Models\News\News();
        $oNews->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oNews->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
