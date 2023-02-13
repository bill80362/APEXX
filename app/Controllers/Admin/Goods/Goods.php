<?php

namespace App\Controllers\Admin\Goods;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Goods extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/goods";
    public function getList()
    {
        // 23.0109 彥佐修改，因後台商品資料原本沒有帶庫存數量，客戶希望商品列表能顯示庫存數量
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->select("Goods.*,SUM(GoodsStock.Stock) AS StockSum,SUM(CustomGoodsStock.Stock) AS CustomGoodsStockSum");
        $oGoods->withDeleted();
        $oGoods->join("GoodsStock", "GoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->join("CustomGoodsStock", "CustomGoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->groupBy("Goods.GoodsID");
        $oGoods->orderBy("Goods.Seq");
        $oGoods->orderBy("Goods.GoodsID", "DESC");
        $List = $oGoods->findAll();
        //關聯
        $Menu2GoodsKeyValue = [];
//        $TradeKeyValue = [];
        if (count($List)) {
            //關聯選單
            $GoodsIDArray = array_column($List, "GoodsID");
            $oMenu2Goods  = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->join("Menu", "Menu.MenuID=Menu2Goods.MenuID");
            $oMenu2Goods->whereIn("GoodsID", $GoodsIDArray);
            $Temp = $oMenu2Goods->findAll();
            if (count($Temp) > 0) {
                $Menu2GoodsKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }

            //訂單紀錄
//            $FilterIDArray = array_column($List,"GoodsID");
//            $oSubTrade = new \App\Models\Trade\SubTrade();
//            $oSubTrade->join("Trade","Trade.TradeID=SubTrade.TradeID");
//            $oSubTrade->join("Payment","Payment.PaymentID=Trade.PaymentID");
//            $oSubTrade->join("Shipping","Shipping.ShippingID=Trade.ShippingID");
//            $oSubTrade->whereIn("GoodsID",$FilterIDArray);
//            $Temp = $oSubTrade->findAll();
//            $TradeKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp,"GoodsID");

            // 關聯庫存，23.0109 彥佐加上，因後台商品資料原本沒有帶庫存資料導致訂單取不到庫存資料
            $GoodsStockKeyValue = [];
            $GoodsIDArray       = array_column($List, "GoodsID");
            $oGoodsStock        = new \App\Models\Goods\GoodsStock();
            $oGoodsStock->select("GoodsStock.*,Color.ColorTitle,Size.SizeTitle");
            $oGoodsStock->join("Color", "Color.ColorID=GoodsStock.ColorID");
            $oGoodsStock->join("Size", "Size.SizeID=GoodsStock.SizeID");
            $oGoodsStock->whereIn("GoodsStock.GoodsID", $GoodsIDArray);
            $oGoodsStock->orderBy("GoodsStock.Seq", "ASC");
            $Temp = $oGoodsStock->findAll();
            if (count($Temp) > 0) {
                $GoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
            // 關聯客製化商品庫存
            $CustomGoodsStockKeyValue = [];
            $GoodsIDArray       = array_column($List, "GoodsID");
            $oCustomGoodsStock  = new \App\Models\CustomGoods\CustomGoodsStock();
            $oCustomGoodsStock->whereIn("CustomGoodsStock.GoodsID", $GoodsIDArray);
            $Temp = $oCustomGoodsStock->findAll();
            if (count($Temp) > 0) {
                $CustomGoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //放入資料
        foreach ($List as $key => $Data) {
            $List[$key]["Menu"] = $Menu2GoodsKeyValue[$List[$key]["GoodsID"]] ?? [];
            // 關聯庫存，23.0109 彥佐加上，因後台商品資料原本沒有帶庫存資料導致訂單取不到庫存資料
            $List[$key]["Stock"] = $GoodsStockKeyValue[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["CustomGoodsStock"] = $CustomGoodsStockKeyValue[$List[$key]["GoodsID"]] ?? [];
//            $List[$key]["TradeList"] = $TradeKeyValue[$List[$key]["GoodsID"]]??[];
        }
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $Image1          = $this->request->getVar("Image1");
        $Image2          = $this->request->getVar("Image2");
        $Seq             = $this->request->getVar("Seq");
        $Title           = $this->request->getVar("Title");
        $Description     = $this->request->getVar("Description");
        $Memo1           = $this->request->getVar("Memo1");
        $Memo2           = $this->request->getVar("Memo2");
        $Memo3           = $this->request->getVar("Memo3");
        $Option1         = $this->request->getVar("Option1");
        $Option2         = $this->request->getVar("Option2");
        $Status          = $this->request->getVar("Status");
        $IsCustom        = $this->request->getVar("IsCustom");
        $CombineDiscount = $this->request->getVar("CombineDiscount");
        $DeliveryFrozen  = $this->request->getVar("DeliveryFrozen");
        $RecommendMenuID = $this->request->getVar("RecommendMenuID");
        $GoodsTimeStart  = $this->request->getVar("GoodsTimeStart");
        $GoodsTimeEnd    = $this->request->getVar("GoodsTimeEnd");
        //檢查選單
        if ($RecommendMenuID) {
            $oMenu    = new \App\Models\Menu\Menu();
            $MenuData = $oMenu->find($RecommendMenuID);
            if (!$MenuData) {
                return $this->respond(ResponseData::fail("選單編號錯誤"));
            }
        }
        //
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->protect(false);
        $GoodsID = $oGoods->insert([
            "Image1"          => $Image1,
            "Image2"          => $Image2,
            "Seq"             => $Seq,
            "Title"           => $Title,
            "Description"     => $Description,
            "Memo1"           => $Memo1,
            "Memo2"           => $Memo2,
            "Memo3"           => $Memo3,
            "Option1"         => $Option1,
            "Option2"         => $Option2,
            "Status"          => $Status,
            "IsCustom"        => $IsCustom,
            "CombineDiscount" => $CombineDiscount,
            "DeliveryFrozen"  => $DeliveryFrozen,
            "RecommendMenuID" => $RecommendMenuID,
            //限制開賣時間
            "GoodsTimeStart"  => $GoodsTimeStart ?: null,
            "GoodsTimeEnd"    => $GoodsTimeEnd ?: null,
        ]);
        if ($oGoods->errors()) {
            $ErrorMsg = implode(",", $oGoods->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oGoods->find($GoodsID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID              = $this->request->getVar("ID");
        $Image1          = $this->request->getVar("Image1");
        $Image2          = $this->request->getVar("Image2");
        $Seq             = $this->request->getVar("Seq");
        $Title           = $this->request->getVar("Title");
        $Description     = $this->request->getVar("Description");
        $Memo1           = $this->request->getVar("Memo1");
        $Memo2           = $this->request->getVar("Memo2");
        $Memo3           = $this->request->getVar("Memo3");
        $Option1         = $this->request->getVar("Option1");
        $Option2         = $this->request->getVar("Option2");
        $Status          = $this->request->getVar("Status");
        $IsCustom        = $this->request->getVar("IsCustom");
        $CombineDiscount = $this->request->getVar("CombineDiscount");
        $DeliveryFrozen  = $this->request->getVar("DeliveryFrozen");
        $RecommendMenuID = $this->request->getVar("RecommendMenuID");
        $GoodsTimeStart  = $this->request->getVar("GoodsTimeStart");
        $GoodsTimeEnd    = $this->request->getVar("GoodsTimeEnd");
        //檢查選單
        if ($RecommendMenuID) {
            $oMenu    = new \App\Models\Menu\Menu();
            $MenuData = $oMenu->find($RecommendMenuID);
            if (!$MenuData) {
                return $this->respond(ResponseData::fail("選單編號錯誤"));
            }
        }
        //
        $oGoods = new \App\Models\Goods\Goods();
        //檢查ID
        $Data = $oGoods->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }

        //開始更新
        $oGoods->protect(false);
        $updateData = [
            "Seq"             => $Seq,
            "Title"           => $Title,
            "Description"     => $Description,
            "Memo1"           => $Memo1,
            "Memo2"           => $Memo2,
            "Memo3"           => $Memo3,
            "Option1"         => $Option1,
            "Option2"         => $Option2,
            "Status"          => $Status,
            "IsCustom"        => $IsCustom,
            "CombineDiscount" => $CombineDiscount,
            "DeliveryFrozen"  => $DeliveryFrozen,
            "RecommendMenuID" => $RecommendMenuID,
            //限制開賣時間
            "GoodsTimeStart"  => $GoodsTimeStart ?: null,
            "GoodsTimeEnd"    => $GoodsTimeEnd ?: null,
        ];
        if ($Image1 !== null) {
            //刪除原本圖檔
            if (isset($Data["Image1"]) && $Data["Image1"] != "") {
                $FileHostPath = ROOTPATH . "public" . $Data["Image1"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //更新路徑
            $updateData["Image1"] = $Image1;
        }
        if ($Image2 !== null) {
            //刪除原本圖檔
            if (isset($Data["Image2"]) && $Data["Image2"] != "") {
                $FileHostPath = ROOTPATH . "public" . $Data["Image2"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //更新路徑
            $updateData["Image2"] = $Image2;
        }
        $oGoods->update($ID, $updateData);
        if ($oGoods->errors()) {
            $ErrorMsg = implode(",", $oGoods->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oGoods->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oGoods = new \App\Models\Goods\Goods();
        //檢查ID
        $Data = $oGoods->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }

        //刪除原本圖檔
        if (isset($Data["Image1"]) && $Data["Image1"] != "") {
            $FileHostPath = ROOTPATH . "public" . $Data["Image1"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除原本圖檔
        if (isset($Data["Image2"]) && $Data["Image2"] != "") {
            $FileHostPath = ROOTPATH . "public" . $Data["Image2"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //刪除DB
        $oGoods->protect(false);
        $oGoods->delete($ID);
        if ($oGoods->errors()) {
            $ErrorMsg = implode(",", $oGoods->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //刪除購物車內的商品
        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
        $oShoppingCart->where("GoodsID", $ID);
        $List = $oShoppingCart->findAll();
        foreach ($List as $value) {
            $oShoppingCart->resetQuery();
            $oShoppingCart->delete($value["ShoppingCardID"]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oGoods = new \App\Models\Goods\Goods();
        //檢查ID
        $Data = $oGoods->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }

        //上傳圖片 Image 多張
        for ($i = 1; $i <= 2; $i++) {
            $file = $this->request->getFile('Image' . $i);
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb') > 5) {
                    return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                }

                if (!in_array($file->getMimeType(), ["image/jpg", "image/png", "image/gif", "image/jpeg", "image/webp"], true)) {
                    return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));
                }

                //刪除原本圖片
                if (isset($Data["Image" . $i]) && $Data["Image" . $i] != "") {
                    $FileHostPath = ROOTPATH . "public" . $Data["Image" . $i];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH . "/public" . $this->ImageDirPath, $name);
                //更新DB
                $oGoods->resetQuery();
                $oGoods->protect(false);
                $oGoods->update($ID, ["Image" . $i => $this->ImageDirPath . "/" . $name]);
            }
        }
        //Res
        $Data = $oGoods->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("資料須為陣列"));
        }

        //更新排序
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->protect(false);
        foreach ($SeqArray as $key => $Data) {
            $oGoods->update($Data->ID, ["Seq" => $Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
