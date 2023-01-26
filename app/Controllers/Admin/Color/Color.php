<?php

namespace App\Controllers\Admin\Color;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Color extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //
        $oColor = new \App\Models\Color\Color();
        $oColor->orderBy("ColorID", "DESC");
        $List = $oColor->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $ColorTitle = $this->request->getVar("ColorTitle");
        $GoodsID = $this->request->getVar("GoodsID");
        $Status = $this->request->getVar("Status");
        //
        $oColor = new \App\Models\Color\Color();
        $oColor->protect(false);
        $ColorID = $oColor->insert([
            "ColorTitle"=>$ColorTitle,
            "GoodsID"=>$GoodsID,
            "Status"=>$Status,
        ]);
        if ($oColor->errors()) {
            $ErrorMsg = implode(",", $oColor->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oColor->find($ColorID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $ColorTitle = $this->request->getVar("ColorTitle");
        $GoodsID = $this->request->getVar("GoodsID");
        $Status = $this->request->getVar("Status");
        //
        $oColor = new \App\Models\Color\Color();
        //檢查ID
        $Data = $oColor->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oColor->protect(false);
        $updateData = [
            "ColorTitle"=>$ColorTitle,
            "GoodsID"=>$GoodsID,
            "Status"=>$Status,
        ];
        $oColor->update($ID, $updateData);
        if ($oColor->errors()) {
            $ErrorMsg = implode(",", $oColor->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oColor->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oColor = new \App\Models\Color\Color();
        //檢查ID
        $Data = $oColor->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //刪除DB
        $oColor->protect(false);
        $oColor->delete($ID);
        if ($oColor->errors()) {
            $ErrorMsg = implode(",", $oColor->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
