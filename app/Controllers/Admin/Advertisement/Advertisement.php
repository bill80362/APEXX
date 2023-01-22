<?php

namespace App\Controllers\Admin\Advertisement;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Advertisement extends BaseController
{
    public $ImageDirPath = "/image/advertisement";
    use ResponseTrait;
    public function getList(){
        //
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        $oAdvertisement->orderBy("Seq");
        $oAdvertisement->orderBy("AdvertisementID","DESC");
        $List = $oAdvertisement->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Content = $this->request->getVar("Content");
        $Seq = $this->request->getVar("Seq");
        //檢查目錄ID
        $oCategory = new \App\Models\Advertisement\Category();
        $Data = $oCategory->find($CategoryID);
        if(!$Data) return $this->respond(ResponseData::fail("目錄ID有誤"));
        //
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        $oAdvertisement->protect(false);
        $AdvertisementID = $oAdvertisement->insert([
            "AdvertisementCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Content"=>$Content,
            "Seq"=>$Seq,
        ]);
        if($oAdvertisement->errors()){
            $ErrorMsg = implode(",",$oAdvertisement->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oAdvertisement->find($AdvertisementID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $Content = $this->request->getVar("Content");
        $Seq = $this->request->getVar("Seq");
        //
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        //檢查ID
        $Data = $oAdvertisement->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oAdvertisement->protect(false);
        $oAdvertisement->update($ID,[
            "Title"=>$Title,
            "Content"=>$Content,
            "Seq"=>$Seq,
        ]);
        if($oAdvertisement->errors()){
            $ErrorMsg = implode(",",$oAdvertisement->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oAdvertisement->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        //檢查ID
        $Data = $oAdvertisement->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始刪除
        $oAdvertisement->protect(false);
        $oAdvertisement->delete($ID);
        if($oAdvertisement->errors()){
            $ErrorMsg = implode(",",$oAdvertisement->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID){
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        //檢查ID
        $Data = $oAdvertisement->find($ID);
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
                $oAdvertisement->resetQuery();
                $oAdvertisement->protect(false);
                $oAdvertisement->update($ID,["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oAdvertisement->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        $oAdvertisement->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oAdvertisement->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
