<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Blacklist extends BaseController
{
    use ResponseTrait;
    public function getList($GoodsID)
    {
        //
        $oBlacklist= new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();
        $oBlacklist->where("GoodsID", $GoodsID);
        $oBlacklist->orderBy("BlacklistID");
        $List = $oBlacklist->findAll();

        foreach ($List as $key=>$Data) {
            // 依據關聯的"客製規格編號"，找出"客製規格和分類列表"資料
            $CustomSpecIDArray = explode(",", $List[$key]["CustomSpecID"]);
            if (count($CustomSpecIDArray)>0) {
                $List[$key]["CustomSpecList"] = \App\Controllers\Admin\CustomGoods\Common::findCustomSpecList($List[$key]["GoodsID"], [], $CustomSpecIDArray);
            } else {
                $List[$key]["CustomSpecList"] = [];
            }
        }

        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $CustomSpecID = $this->request->getVar("CustomSpecID");

        //檢查ID
        if ($GoodsID) {
            $oGoods = new \App\Models\Goods\Goods();
            $GoodsData = $oGoods->find($GoodsID);
            if (!$GoodsData) {
                return $this->respond(ResponseData::fail("商品ID有誤"));
            }
        }
        // 檢查每個"客製規格編號"
        if ($CustomSpecID) {
            $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            $CustomSpecIDArray = explode(",", $CustomSpecID);
            foreach ($CustomSpecIDArray as $Data) {
                $oSpec->resetQuery();
                $SpecData = $oSpec->find($Data);
                if (!$SpecData) {
                    return $this->respond(ResponseData::fail("客製規格編號(CustomSpecID):[".$Data."]有誤"));
                }
            }
        }

        $oBlacklist = new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();

        $oBlacklist->protect(false);
        //新增DB
        $ID = $oBlacklist->insert([
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
        ]);
        if ($oBlacklist->errors()) {
            $ErrorMsg = implode(",", $oBlacklist->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "BlacklistID"=>$ID,
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
        ];

        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $GoodsID = $this->request->getVar("GoodsID");
        $CustomSpecID = $this->request->getVar("CustomSpecID");
        //檢查商品ID
        if ($GoodsID) {
            $oGoods = new \App\Models\Goods\Goods();
            $GoodsData = $oGoods->find($GoodsID);
            if (!$GoodsData) {
                return $this->respond(ResponseData::fail("商品ID有誤"));
            }
        }
        // 檢查每個"客製規格編號"
        if ($CustomSpecID) {
            $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            $CustomSpecIDArray = explode(",", $CustomSpecID);
            foreach ($CustomSpecIDArray as $Data) {
                $oSpec->resetQuery();
                $SpecData = $oSpec->find($Data);
                if (!$SpecData) {
                    return $this->respond(ResponseData::fail("客製規格編號(CustomSpecID):[".$Data."]有誤"));
                }
            }
        }
        //
        $oBlacklist = new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();
        //檢查ID
        $Data = $oBlacklist->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oBlacklist->protect(false);
        $oBlacklist->update($ID, [
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
        ]);
        if ($oBlacklist->errors()) {
            $ErrorMsg = implode(",", $oBlacklist->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "BlacklistID"=>$ID,
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oBlacklist = new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();
        //檢查ID
        $Data = $oBlacklist->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oBlacklist->protect(false);
        $oBlacklist->delete($ID);
        if ($oBlacklist->errors()) {
            $ErrorMsg = implode(",", $oBlacklist->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
