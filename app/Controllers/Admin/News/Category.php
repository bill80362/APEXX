<?php

namespace App\Controllers\Admin\News;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Category extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $oCategory = new \App\Models\News\Category();
        $oCategory->orderBy("Seq");
        $oCategory->orderBy("NewsCategoryID", "DESC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        //
        $oCategory = new \App\Models\News\Category();
        $oCategory->protect(false);
        $InsertID = $oCategory->insert([
           "Title"=>$Title,
           "Seq"=>$Seq,
        ]);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "NewsCategoryID"=>$InsertID,
            "Title"=>$Title,
            "Seq"=>$Seq,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $Seq = $this->request->getVar("Seq");
        //
        $oCategory = new \App\Models\News\Category();
        //檢查ID
        $Data = $oCategory->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oCategory->protect(false);
        $oCategory->update($ID, [
            "Title"=>$Title,
            "Seq"=>$Seq,
        ]);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "NewsCategoryID"=>$ID,
            "Title"=>$Title,
            "Seq"=>$Seq,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oCategory = new \App\Models\News\Category();
        //檢查ID
        $Data = $oCategory->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oCategory->protect(false);
        $oCategory->delete($ID);
        if ($oCategory->errors()) {
            $ErrorMsg = implode(",", $oCategory->errors());
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
        $oCategory = new \App\Models\News\Category();
        $oCategory->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oCategory->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
