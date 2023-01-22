<?php

namespace App\Controllers\Admin\Size;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Size extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //
        $oSize = new \App\Models\Size\Size();
        $oSize->orderBy("SizeID","DESC");
        $List = $oSize->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //
        $SizeTitle = $this->request->getVar("SizeTitle");
        $GoodsID = $this->request->getVar("GoodsID");
        $Status = $this->request->getVar("Status");
        //
        $oSize = new \App\Models\Size\Size();
        $oSize->protect(false);
        $SizeID = $oSize->insert([
            "SizeTitle"=>$SizeTitle,
            "GoodsID"=>$GoodsID,
            "Status"=>$Status,
        ]);
        if($oSize->errors()){
            $ErrorMsg = implode(",",$oSize->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oSize->find($SizeID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update(){
        //
        $ID = $this->request->getVar("ID");
        $SizeTitle = $this->request->getVar("SizeTitle");
        $GoodsID = $this->request->getVar("GoodsID");
        $Status = $this->request->getVar("Status");
        //
        $oSize = new \App\Models\Size\Size();
        //檢查ID
        $Data = $oSize->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //開始更新
        $oSize->protect(false);
        $updateData = [
            "SizeTitle"=>$SizeTitle,
            "GoodsID"=>$GoodsID,
            "Status"=>$Status,
        ];
        $oSize->update($ID,$updateData);
        if($oSize->errors()){
            $ErrorMsg = implode(",",$oSize->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oSize->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //
        $oSize = new \App\Models\Size\Size();
        //檢查ID
        $Data = $oSize->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //刪除DB
        $oSize->protect(false);
        $oSize->delete($ID);
        if($oSize->errors()){
            $ErrorMsg = implode(",",$oSize->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
