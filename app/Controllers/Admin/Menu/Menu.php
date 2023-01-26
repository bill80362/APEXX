<?php

namespace App\Controllers\Admin\Menu;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Menu extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/menu";
    public function getList()
    {
        //
        $oMenu = new \App\Models\Menu\Menu();
        $oMenu->orderBy("Seq");
        $oMenu->orderBy("MenuID", "DESC");
        $List = $oMenu->findAll();
        //關聯選單
        $Menu2GoodsKeyValue = [];
        if (count($List)) {
            $MenuIDArray = array_column($List, "MenuID");
            $oMenu2Goods = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->join("Goods", "Goods.GoodsID=Menu2Goods.GoodsID");
            $oMenu2Goods->whereIn("Menu2Goods.MenuID", $MenuIDArray);
            $Temp = $oMenu2Goods->findAll();
            if (count($Temp)>0) {
                $Menu2GoodsKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "MenuID");
            }
        }
        //放入資料
        foreach ($List as $key=>$Data) {
            $List[$key]["Goods"] = $Menu2GoodsKeyValue[$List[$key]["MenuID"]]??[];
        }
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $CategoryID = $this->request->getVar("CategoryID");
        $Title = $this->request->getVar("Title");
        $Subtitle = $this->request->getVar("Subtitle");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        $Content = $this->request->getVar("Content");
        $Content2 = $this->request->getVar("Content2");
        $Content3 = $this->request->getVar("Content3");
        $Content4 = $this->request->getVar("Content4");
        $Content5 = $this->request->getVar("Content5");
        $Content6 = $this->request->getVar("Content6");
        $Content7 = $this->request->getVar("Content7");
        $MenuTimeStart = $this->request->getVar("MenuTimeStart");
        $MenuTimeEnd = $this->request->getVar("MenuTimeEnd");
        //檢查目錄ID
        $oCategory = new \App\Models\Menu\Category();
        $Data = $oCategory->find($CategoryID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("目錄ID有誤"));
        }
        //
        $oMenu = new \App\Models\Menu\Menu();
        $oMenu->protect(false);
        $MenuID = $oMenu->insert([
            "MenuCategoryID"=>$CategoryID,
            "Title"=>$Title,
            "Subtitle"=>$Subtitle,
            "Seq"=>$Seq,
            "Status"=>$Status,
            "Content"=>$Content,
            "Content2"=>$Content2,
            "Content3"=>$Content3,
            "Content4"=>$Content4,
            "Content5"=>$Content5,
            "Content6"=>$Content6,
            "Content7"=>$Content7,
            "MenuTimeStart"=>$MenuTimeStart,
            "MenuTimeEnd"=>$MenuTimeEnd,
        ]);
        if ($oMenu->errors()) {
            $ErrorMsg = implode(",", $oMenu->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oMenu->find($MenuID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Title = $this->request->getVar("Title");
        $Subtitle = $this->request->getVar("Subtitle");
        $Seq = $this->request->getVar("Seq");
        $Status = $this->request->getVar("Status");
        $Content = $this->request->getVar("Content");
        $Content2 = $this->request->getVar("Content2");
        $Content3 = $this->request->getVar("Content3");
        $Content4 = $this->request->getVar("Content4");
        $Content5 = $this->request->getVar("Content5");
        $Content6 = $this->request->getVar("Content6");
        $Content7 = $this->request->getVar("Content7");
        $MenuTimeStart = $this->request->getVar("MenuTimeStart");
        $MenuTimeEnd = $this->request->getVar("MenuTimeEnd");
        //
        $oMenu = new \App\Models\Menu\Menu();
        //檢查ID
        $Data = $oMenu->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始更新
        $oMenu->protect(false);
        $oMenu->update($ID, [
            "Title"=>$Title,
            "Subtitle"=>$Subtitle,
            "Seq"=>$Seq,
            "Status"=>$Status,
            "Content"=>$Content,
            "Content2"=>$Content2,
            "Content3"=>$Content3,
            "Content4"=>$Content4,
            "Content5"=>$Content5,
            "Content6"=>$Content6,
            "Content7"=>$Content7,
            "MenuTimeStart"=>$MenuTimeStart,
            "MenuTimeEnd"=>$MenuTimeEnd,
        ]);
        if ($oMenu->errors()) {
            $ErrorMsg = implode(",", $oMenu->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oMenu->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oMenu = new \App\Models\Menu\Menu();
        //檢查ID
        $Data = $oMenu->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //開始刪除
        $oMenu->protect(false);
        $oMenu->delete($ID);
        if ($oMenu->errors()) {
            $ErrorMsg = implode(",", $oMenu->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oMenu = new \App\Models\Menu\Menu();
        //檢查ID
        $Data = $oMenu->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //上傳圖片 Image 多張
        for ($i=1;$i<=1;$i++) {
            $file = $this->request->getFile('Image'.$i);
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb')>5) {
                    return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                }
                if (!in_array($file->getMimeType(), ["image/jpg","image/png","image/gif","image/jpeg","image/webp"], true)) {
                    return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));
                }                //刪除原本圖片
                if (isset($Data["Image".$i]) && $Data["Image".$i]!="") {
                    $FileHostPath = ROOTPATH."public".$Data["Image".$i];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //更新DB
                $oMenu->resetQuery();
                $oMenu->protect(false);
                $oMenu->update($ID, ["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oMenu->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }
        //更新排序
        $oMenu = new \App\Models\Menu\Menu();
        $oMenu->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oMenu->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
