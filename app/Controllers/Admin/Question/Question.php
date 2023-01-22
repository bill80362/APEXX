<?php

namespace App\Controllers\Admin\Question;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Question extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //
        $oQuestion = new \App\Models\Question\Question();
        $oQuestion->orderBy("Seq");
        $oQuestion->orderBy("QuestionID","DESC");
        $List = $oQuestion->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Content = $this->request->getVar("Content");
        //檢查目錄ID
        $oCategory = new \App\Models\Question\Category();
        $Data = $oCategory->find($CategoryID);
        if(!$Data) return $this->respond(ResponseData::fail("目錄ID有誤"));
        //
        $oQuestion = new \App\Models\Question\Question();
        $oQuestion->protect(false);
        $QuestionID = $oQuestion->insert([
            "QuestionCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Content"=>$Content,
        ]);
        if($oQuestion->errors()){
            $ErrorMsg = implode(",",$oQuestion->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oQuestion->find($QuestionID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        $Content = $this->request->getVar("Content");
        //
        $oQuestion = new \App\Models\Question\Question();
        //檢查ID
        $Data = $oQuestion->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oQuestion->protect(false);
        $oQuestion->update($ID,[
            "QuestionCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Seq"=>$Seq,
            "Content"=>$Content,
        ]);
        if($oQuestion->errors()){
            $ErrorMsg = implode(",",$oQuestion->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oQuestion->find($ID);
        return $this->respond(ResponseData::success($Data));

    }
    public function del($ID){
        //
        $oQuestion = new \App\Models\Question\Question();
        //檢查ID
        $Data = $oQuestion->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始刪除
        $oQuestion->protect(false);
        $oQuestion->delete($ID);
        if($oQuestion->errors()){
            $ErrorMsg = implode(",",$oQuestion->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oQuestion = new \App\Models\Question\Question();
        $oQuestion->protect(false);
        foreach ($SeqArray as $key=>$Data){
            $oQuestion->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
