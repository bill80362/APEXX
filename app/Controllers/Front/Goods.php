<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Goods extends BaseController
{
    use ResponseTrait;

    public function getList()
    {
        //Filter
        $FilterInStock = $this->request->getVar("FilterInStock") ?? "N";
        $FilterMenuID  = $this->request->getVar("FilterMenuID") ?? "";
        //過濾Menu對應的GoodsID
        $FilterGoodsIDArray = [];
        if ($FilterMenuID) {
            $oMenu2Goods = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->where("MenuID", $FilterMenuID);
            $Temp = $oMenu2Goods->findAll();
            if ($Temp) {
                $FilterGoodsIDArray = array_column($Temp, "GoodsID");
            }
        }
        //
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->select("Goods.*,SUM(GoodsStock.Stock) AS StockSum,SUM(CustomGoodsStock.Stock) AS CustomGoodsStockSum");
        $oGoods->withDeleted();
        $oGoods->join("GoodsStock", "GoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->join("CustomGoodsStock", "CustomGoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->groupBy("Goods.GoodsID");
        $oGoods->orderBy("Goods.Seq");
        $oGoods->orderBy("Goods.GoodsID", "DESC");
        //
        // $oGoods->where("Goods.Status","Y");//開啟 //23.01.09 彥佐修改，過濾Status會導致訂單查詢商品資料時找不到已停用的商品資料
        //篩選條件
        if (count($FilterGoodsIDArray) > 0) {
            $oGoods->whereIn("Goods.GoodsID", $FilterGoodsIDArray);
        }

        if ($FilterInStock == "Y") {
            $oGoods->having("StockSum >", 0);
            $oGoods->orHaving("CustomGoodsStockSum >", 0);
        }

        //
        $List = $oGoods->findAll();
        //關聯選單
        $Menu2GoodsKeyValue = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $oMenu2Goods  = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->join("Menu", "Menu.MenuID=Menu2Goods.MenuID");
            $oMenu2Goods->whereIn("GoodsID", $GoodsIDArray);
            $Temp = $oMenu2Goods->findAll();
            if (count($Temp) > 0) {
                $Menu2GoodsKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //關聯庫存
        $GoodsStockKeyValue = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $oGoodsStock  = new \App\Models\Goods\GoodsStock();
            $oGoodsStock->select("GoodsStock.*,Color.ColorTitle,Size.SizeTitle");
            $oGoodsStock->join("Color", "Color.ColorID=GoodsStock.ColorID");
            $oGoodsStock->join("Size", "Size.SizeID=GoodsStock.SizeID");
            $oGoodsStock->whereIn("GoodsStock.GoodsID", $GoodsIDArray);
            $oGoodsStock->orderBy("GoodsStock.Seq", "ASC");
            $Temp = $oGoodsStock->findAll();
            if (count($Temp) > 0) {
                $GoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //關聯客製化商品庫存
        $CustomGoodsStockKeyValue = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $oCustomGoodsStock  = new \App\Models\CustomGoods\CustomGoodsStock();
            $oCustomGoodsStock->whereIn("CustomGoodsStock.GoodsID", $GoodsIDArray);
            $Temp = $oCustomGoodsStock->findAll();
            if (count($Temp) > 0) {
                $CustomGoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //關聯"客製規格和分類列表"資料
        $CustomSpecListKeyValue = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $Temp = \App\Libraries\CustomGoods::findCustomSpecList($GoodsIDArray, [], []);
            if (count($Temp) > 0) {
                $CustomSpecListKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //關聯客製化商品規格黑名單
        $CustomGoodsSpecBlacklistKV = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $oCustomGoodsSpecBlacklist = new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();
            $oCustomGoodsSpecBlacklist->whereIn("CustomGoodsSpecBlacklist.GoodsID", $GoodsIDArray);
            $oCustomGoodsSpecBlacklist->orderBy("CustomGoodsSpecBlacklist.BlacklistID", "ASC");
            $Temp = $oCustomGoodsSpecBlacklist->findAll();
            if (count($Temp) > 0) {
                $CustomGoodsSpecBlacklistKV = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //客製化商品規格組合異動價
        $CustomGoodsChangePriceKV = [];
        if (count($List)) {
            $GoodsIDArray = array_column($List, "GoodsID");
            $oCustomGoodsChangePrice = new \App\Models\CustomGoods\CustomGoodsChangePrice();
            $oCustomGoodsChangePrice->whereIn("CustomGoodsChangePrice.GoodsID", $GoodsIDArray);
            $oCustomGoodsChangePrice->orderBy("CustomGoodsChangePrice.ChangePriceID", "ASC");
            $Temp = $oCustomGoodsChangePrice->findAll();
            if (count($Temp) > 0) {
                $CustomGoodsChangePriceKV = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
            }
        }
        //關聯圖片
//        $GoodsPictureKeyValue = [];
//        if(count($List)){
//            $GoodsIDArray = array_column($List,"GoodsID");
//            $oGoodsPicture = new \App\Models\Goods\GoodsPicture();
//            $oGoodsPicture->whereIn("GoodsPicture.GoodsID",$GoodsIDArray);
//            $oGoodsPicture->orderBy("GoodsPicture.Seq","ASC");
//            $Temp = $oGoodsPicture->findAll();
//            if(count($Temp)>0)
//                $GoodsPictureKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp,"GoodsID");
//        }
        //放入資料
        foreach ($List as $key => $Data) {
            $List[$key]["Menu"]  = $Menu2GoodsKeyValue[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["Stock"] = $GoodsStockKeyValue[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["CustomGoodsStock"] = $CustomGoodsStockKeyValue[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["CustomSpecList"] = $CustomSpecListKeyValue[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["CustomGoodsSpecBlacklist"] = $CustomGoodsSpecBlacklistKV[$List[$key]["GoodsID"]] ?? [];
            $List[$key]["CustomGoodsChangePrice"] = $CustomGoodsChangePriceKV[$List[$key]["GoodsID"]] ?? [];
//            $List[$key]["Picture"] = $GoodsPictureKeyValue[$List[$key]["GoodsID"]]??[];
        }

        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getData($GoodsID)
    {
        //商品ID
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->select("Goods.*,SUM(GoodsStock.Stock) AS StockSum,SUM(CustomGoodsStock.Stock) AS CustomGoodsStockSum");
        $oGoods->join("GoodsStock", "GoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->join("CustomGoodsStock", "CustomGoodsStock.GoodsID=Goods.GoodsID", "left");
        $oGoods->groupBy("Goods.GoodsID");
        $Data = $oGoods->find($GoodsID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("商品編號錯誤"));
        }

        //關聯選單
        $Menu2GoodsKeyValue = [];
        $GoodsIDArray       = [$Data["GoodsID"]];
        $oMenu2Goods        = new \App\Models\Menu2Goods\Menu2Goods();
        $oMenu2Goods->join("Menu", "Menu.MenuID=Menu2Goods.MenuID");
        $oMenu2Goods->where("Menu.Status", "Y");
        $oMenu2Goods->whereIn("GoodsID", $GoodsIDArray);
        $Temp = $oMenu2Goods->findAll();
        if (count($Temp) > 0) {
            $Menu2GoodsKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //關聯庫存
        $GoodsStockKeyValue = [];
        $GoodsIDArray       = [$Data["GoodsID"]];
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

        //關聯圖片
        $GoodsPictureKeyValue = [];
        $GoodsIDArray         = [$Data["GoodsID"]];
        $oGoodsPicture        = new \App\Models\Goods\GoodsPicture();
        $oGoodsPicture->whereIn("GoodsPicture.GoodsID", $GoodsIDArray);
        $oGoodsPicture->orderBy("GoodsPicture.Seq", "ASC");
        $Temp = $oGoodsPicture->findAll();
        if (count($Temp) > 0) {
            $GoodsPictureKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //關聯優惠 透過MenuID
        $GoodsDiscountKeyValue = [];
        $GoodsIDArray          = [$Data["GoodsID"]];
        $oGoodsDiscount        = new \App\Models\Discount\Discount();
        $oGoodsDiscount->select("Menu2Goods.*,Menu.*,Discount.*");
        $oGoodsDiscount->join("Menu", "Menu.MenuID=Discount.MenuID");
        $oGoodsDiscount->join("Menu2Goods", "Menu2Goods.MenuID=Discount.MenuID", "left");
        $oGoodsDiscount->where("Menu.Status", "Y");
        $oGoodsDiscount->whereIn("Menu2Goods.GoodsID", $GoodsIDArray);
        $Temp = $oGoodsDiscount->findAll();
        if (count($Temp) > 0) {
            $GoodsDiscountKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //關聯客製化商品庫存
        $CustomGoodsStockKeyValue = [];
        $GoodsIDArray       = [$Data["GoodsID"]];
        $oCustomGoodsStock  = new \App\Models\CustomGoods\CustomGoodsStock();
        $oCustomGoodsStock->whereIn("CustomGoodsStock.GoodsID", $GoodsIDArray);
        $Temp = $oCustomGoodsStock->findAll();
        if (count($Temp) > 0) {
            $CustomGoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //關聯"客製規格和分類列表"資料
        $CustomSpecListKeyValue = [];
        $GoodsIDArray = [$Data["GoodsID"]];
        $Temp = \App\Libraries\CustomGoods::findCustomSpecList($GoodsIDArray, [], []);
        if (count($Temp) > 0) {
            //關聯客製規格圖片
            $CustomGoodsSpecPictureKV = [];
            $CustomSpecIDArray = array_column($Temp, "CustomSpecID");
            $oCustomGoodsSpecPicture = new \App\Models\CustomGoods\CustomGoodsSpecPicture();
            $oCustomGoodsSpecPicture->whereIn("CustomGoodsSpecPicture.CustomSpecID", $CustomSpecIDArray);
            $oCustomGoodsSpecPicture->orderBy("CustomGoodsSpecPicture.Seq", "ASC");
            $oCustomGoodsSpecPicture->orderBy("CustomGoodsSpecPicture.SpecPictureID", "ASC");
            $Temp2 = $oCustomGoodsSpecPicture->findAll();
            if (count($Temp2) > 0) {
                $CustomGoodsSpecPictureKV = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp2, "CustomSpecID");
            }
            //放入資料
            foreach ($Temp as $key => $Picture) {
                $Temp[$key]["CustomGoodsSpecPicture"] = $CustomGoodsSpecPictureKV[$Temp[$key]["CustomSpecID"]] ?? [];
            }
            $CustomSpecListKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //關聯客製化商品規格黑名單
        $CustomGoodsSpecBlacklistKV = [];
        $GoodsIDArray = [$Data["GoodsID"]];
        $oCustomGoodsSpecBlacklist = new \App\Models\CustomGoods\CustomGoodsSpecBlacklist();
        $oCustomGoodsSpecBlacklist->whereIn("CustomGoodsSpecBlacklist.GoodsID", $GoodsIDArray);
        $oCustomGoodsSpecBlacklist->orderBy("CustomGoodsSpecBlacklist.BlacklistID", "ASC");
        $Temp = $oCustomGoodsSpecBlacklist->findAll();
        if (count($Temp) > 0) {
            $CustomGoodsSpecBlacklistKV = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //客製化商品規格組合異動價
        $CustomGoodsChangePriceKV = [];
        $GoodsIDArray = [$Data["GoodsID"]];
        $oCustomGoodsChangePrice = new \App\Models\CustomGoods\CustomGoodsChangePrice();
        $oCustomGoodsChangePrice->whereIn("CustomGoodsChangePrice.GoodsID", $GoodsIDArray);
        $oCustomGoodsChangePrice->orderBy("CustomGoodsChangePrice.ChangePriceID", "ASC");
        $Temp = $oCustomGoodsChangePrice->findAll();
        if (count($Temp) > 0) {
            $CustomGoodsChangePriceKV = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //放入資料
        $Data["Menu"]     = $Menu2GoodsKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Stock"]    = $GoodsStockKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Picture"]  = $GoodsPictureKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Discount"] = $GoodsDiscountKeyValue[$Data["GoodsID"]] ?? [];
        $Data["CustomGoodsStock"] = $CustomGoodsStockKeyValue[$Data["GoodsID"]] ?? [];
        $Data["CustomSpecList"] = $CustomSpecListKeyValue[$Data["GoodsID"]] ?? [];
        $Data["CustomGoodsSpecBlacklist"] = $CustomGoodsSpecBlacklistKV[$Data["GoodsID"]] ?? [];
        $Data["CustomGoodsChangePrice"] = $CustomGoodsChangePriceKV[$Data["GoodsID"]] ?? [];
        //Res
        return $this->respond(ResponseData::success($Data));
    }
}
