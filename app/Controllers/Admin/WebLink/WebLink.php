<?php

namespace App\Controllers\Admin\WebLink;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class WebLink extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //
        $oWebLink = new \App\Models\WebLink\WebLink();
        $oWebLink->orderBy("Seq");
        $oWebLink->orderBy("WebLinkID","DESC");
        $List = $oWebLink->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        $Link = $this->request->getVar("Link");
        //檢查目錄ID
        $oCategory = new \App\Models\WebLink\Category();
        $Data = $oCategory->find($CategoryID);
        if(!$Data) return $this->respond(ResponseData::fail("目錄ID有誤"));
        //
        $oWebLink = new \App\Models\WebLink\WebLink();
        $oWebLink->protect(false);
        $WebLinkID = $oWebLink->insert([
            "WebLinkCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
            "Link"=>$Link,
        ]);
        if($oWebLink->errors()){
            $ErrorMsg = implode(",",$oWebLink->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oWebLink->find($WebLinkID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        $Link = $this->request->getVar("Link");
        //
        $oWebLink = new \App\Models\WebLink\WebLink();
        //檢查ID
        $Data = $oWebLink->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oWebLink->protect(false);
        $oWebLink->update($ID,[
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Status"=>$Status,
            "Link"=>$Link,
        ]);
        if($oWebLink->errors()){
            $ErrorMsg = implode(",",$oWebLink->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oWebLink->find($ID);
        return $this->respond(ResponseData::success($Data));

    }
    public function del($ID){
        //
        $oWebLink = new \App\Models\WebLink\WebLink();
        //檢查ID
        $Data = $oWebLink->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始刪除
        $oWebLink->protect(false);
        $oWebLink->delete($ID);
        if($oWebLink->errors()){
            $ErrorMsg = implode(",",$oWebLink->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oWebLink = new \App\Models\WebLink\WebLink();
        $oWebLink->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oWebLink->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
