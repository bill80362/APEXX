<?php

namespace App\Controllers\Admin\WebLink;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Category extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //
        $oCategory = new \App\Models\WebLink\Category();
        $oCategory->orderBy("Seq");
        $oCategory->orderBy("WebLinkCategoryID","DESC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //
        $oCategory = new \App\Models\WebLink\Category();
        $oCategory->protect(false);
        $InsertID = $oCategory->insert([
           "Title"=>$Title,
           "Seq"=>$Seq,
           "Status"=>$Status,
        ]);
        if($oCategory->errors()){
            $ErrorMsg = implode(",",$oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oCategory->find($InsertID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        //
        $oCategory = new \App\Models\WebLink\Category();
        //檢查ID
        $Data = $oCategory->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oCategory->protect(false);
        $oCategory->update($ID,[
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
        ]);
        if($oCategory->errors()){
            $ErrorMsg = implode(",",$oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oCategory->find($ID);
        return $this->respond(ResponseData::success($Data));

    }
    public function del($ID){
        //
        $oCategory = new \App\Models\WebLink\Category();
        //檢查ID
        $Data = $oCategory->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始刪除
        $oCategory->protect(false);
        $oCategory->delete($ID);
        if($oCategory->errors()){
            $ErrorMsg = implode(",",$oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oCategory = new \App\Models\WebLink\Category();
        $oCategory->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oCategory->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
