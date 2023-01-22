<?php

namespace App\Controllers\Admin\Kol;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Kol extends BaseController
{
    public $ImageDirPath = "/image/kol";
    use ResponseTrait;
    public function getList(){
        //
        $oKol = new \App\Models\Kol\Kol();
        $oKol->orderBy("Seq");
        $oKol->orderBy("KolID","DESC");
        $List = $oKol->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        $SubTitle = $this->request->getVar("SubTitle");
        $Description = $this->request->getVar("Description");
        //
        $oKol = new \App\Models\Kol\Kol();
        $oKol->protect(false);
        $KolID = $oKol->insert([
            "Image1"=>$Image1,
            "Image2"=>$Image2,
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
            "SubTitle"=>$SubTitle,
            "Description"=>$Description,
        ]);
        if($oKol->errors()){
            $ErrorMsg = implode(",",$oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oKol->find($KolID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        $SubTitle = $this->request->getVar("SubTitle");
        $Description = $this->request->getVar("Description");
        //
        $oKol = new \App\Models\Kol\Kol();
        //檢查ID
        $Data = $oKol->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oKol->protect(false);
        $updateData = [
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
            "SubTitle"=>$SubTitle,
            "Description"=>$Description,
        ];
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
        if($Image2!==NULL) {
            //刪除原本圖檔
            if( isset($Data["Image2"]) && $Data["Image2"]!="" ){
                $FileHostPath = ROOTPATH."public".$Data["Image2"];
                if(file_exists($FileHostPath))
                    unlink($FileHostPath);
            }
            //更新路徑
            $updateData["Image2"] = $Image2;
        }
        $oKol->update($ID,$updateData);
        if($oKol->errors()){
            $ErrorMsg = implode(",",$oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oKol->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //
        $oKol = new \App\Models\Kol\Kol();
        //檢查ID
        $Data = $oKol->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //刪除原本圖檔
        if( isset($Data["Image1"]) && $Data["Image1"]!="" ){
            $FileHostPath = ROOTPATH."public".$Data["Image1"];
            if(file_exists($FileHostPath))
                unlink($FileHostPath);
        }
        //刪除原本圖檔
        if( isset($Data["Image2"]) && $Data["Image2"]!="" ){
            $FileHostPath = ROOTPATH."public".$Data["Image2"];
            if(file_exists($FileHostPath))
                unlink($FileHostPath);
        }
        //刪除DB
        $oKol->protect(false);
        $oKol->delete($ID);
        if($oKol->errors()){
            $ErrorMsg = implode(",",$oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID){
        $oKol = new \App\Models\Kol\Kol();
        //檢查ID
        $Data = $oKol->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //上傳圖片 Image 多張
        for ($i=1;$i<=2;$i++){
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
                $oKol->resetQuery();
                $oKol->protect(false);
                $oKol->update($ID,["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oKol->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oKol = new \App\Models\Kol\Kol();
        $oKol->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oKol->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
