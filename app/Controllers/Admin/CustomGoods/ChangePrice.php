<?php

namespace App\Controllers\Admin\CustomGoods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class ChangePrice extends BaseController
{
    use ResponseTrait;
    public function getList($GoodsID)
    {
        //
        $oChangePrice= new \App\Models\CustomGoods\CustomGoodsChangePrice();
        $oChangePrice->where("GoodsID", $GoodsID);
        $oChangePrice->orderBy("ChangePriceID");
        $List = $oChangePrice->findAll();

        foreach ($List as $key=>$Data) {
            // 依據關聯的"客製規格編號"，找出"客製規格和分類列表"資料
            $CustomSpecIDArray = explode(",", $List[$key]["CustomSpecID"]);
            if (count($CustomSpecIDArray)>0) {
                $List[$key]["CustomSpecList"] = \App\Libraries\CustomGoods::findCustomSpecList([$List[$key]["GoodsID"]], [], $CustomSpecIDArray);
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
        $ChangePrice = $this->request->getVar("ChangePrice");
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

        $oChangePrice = new \App\Models\CustomGoods\CustomGoodsChangePrice();

        $oChangePrice->protect(false);
        //新增DB
        $ID = $oChangePrice->insert([
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
            "ChangePrice"=>$ChangePrice,
        ]);
        if ($oChangePrice->errors()) {
            $ErrorMsg = implode(",", $oChangePrice->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "ChangePriceID"=>$ID,
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
            "ChangePrice"=>$ChangePrice,
        ];

        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $GoodsID = $this->request->getVar("GoodsID");
        $CustomSpecID = $this->request->getVar("CustomSpecID");
        $ChangePrice = $this->request->getVar("ChangePrice");
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
        $oChangePrice = new \App\Models\CustomGoods\CustomGoodsChangePrice();
        //檢查ID
        $Data = $oChangePrice->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oChangePrice->protect(false);
        $oChangePrice->update($ID, [
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
            "ChangePrice"=>$ChangePrice,
        ]);
        if ($oChangePrice->errors()) {
            $ErrorMsg = implode(",", $oChangePrice->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = [
            "ChangePriceID"=>$ID,
            "GoodsID"=>$GoodsID,
            "CustomSpecID"=>$CustomSpecID,
            "ChangePrice"=>$ChangePrice,
        ];
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oChangePrice = new \App\Models\CustomGoods\CustomGoodsChangePrice();
        //檢查ID
        $Data = $oChangePrice->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oChangePrice->protect(false);
        $oChangePrice->delete($ID);
        if ($oChangePrice->errors()) {
            $ErrorMsg = implode(",", $oChangePrice->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
