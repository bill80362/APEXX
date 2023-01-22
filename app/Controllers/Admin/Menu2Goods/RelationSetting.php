<?php

namespace App\Controllers\Admin\Menu2Goods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class RelationSetting extends BaseController
{
    use ResponseTrait;

    public function setMenu($MenuID){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //檢查
        $oMenu = new \App\Models\Menu\Menu();
        $Data = $oMenu->find($MenuID);
        if(!$Data) return $this->respond(ResponseData::fail("MenuID有誤"));
        //
        $oMenuSetting = new \App\Models\Menu2Goods\Menu2Goods();
        $oMenuSetting->protect(false);
        //先清空Menu所有GoodsID
        $oMenuSetting->resetQuery();
        $oMenuSetting->where("MenuID",$MenuID);
        $oMenuSetting->delete();
        //再設定上去GoodsID
        $oMenuSetting->resetQuery();
        $InsertData = [];
        foreach ($SeqArray as $key=>$Data){
            //檢查
            $oGoods = new \App\Models\Goods\Goods();
            $CheckData = $oGoods->find($Data);
            if(!$CheckData) continue;
            //新增
            $InsertData[] = [
                "MenuID"=>$MenuID,
                "GoodsID"=>$Data,
            ];
        }
        if($InsertData)
            $oMenuSetting->insertBatch($InsertData);
        //Error
        if($oMenuSetting->errors()){
            $ErrorMsg = implode(",",$oMenuSetting->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success($InsertData));
    }
    public function setGoods($GoodsID){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //檢查
        $oGoods = new \App\Models\Goods\Goods();
        $Data = $oGoods->find($GoodsID);
        if(!$Data) return $this->respond(ResponseData::fail("GoodsID有誤"));
        //
        $oMenuSetting = new \App\Models\Menu2Goods\Menu2Goods();
        $oMenuSetting->protect(false);
        //先清空Menu所有GoodsID
        $oMenuSetting->resetQuery();
        $oMenuSetting->where("GoodsID",$GoodsID);
        $oMenuSetting->delete();
        //再設定上去GoodsID
        $oMenuSetting->resetQuery();
        $InsertData = [];
        foreach ($SeqArray as $key=>$Data){
            //檢查
            $oMenu = new \App\Models\Menu\Menu();
            $CheckData = $oMenu->find($Data);
            if(!$CheckData) continue;
            //新增
            $InsertData[] = [
                "GoodsID"=>$GoodsID,
                "MenuID"=>$Data,
            ];
        }
        if($InsertData)
            $oMenuSetting->insertBatch($InsertData);
        //Error
        if($oMenuSetting->errors()){
            $ErrorMsg = implode(",",$oMenuSetting->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success($InsertData));
    }

    public function add(){
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //
        $oMenuSetting = new \App\Models\Menu2Goods\Menu2Goods();
        $oMenuSetting->protect(false);
        //
        foreach ($SeqArray as $key=>$Data){
            //檢查
            $oMenu = new \App\Models\Menu\Menu();
            $CheckData = $oMenu->find($Data->MenuID);
            if(!$CheckData) continue;
            //檢查
            $oGoods = new \App\Models\Goods\Goods();
            $CheckData = $oGoods->find($Data->GoodsID);
            if(!$CheckData) continue;
            //
            $oMenuSetting->replace([
                "GoodsID"=>$Data->GoodsID,
                "MenuID"=>$Data->MenuID,
            ]);
        }
        //Error
        if($oMenuSetting->errors()){
            $ErrorMsg = implode(",",$oMenuSetting->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success($SeqArray));
    }
}
