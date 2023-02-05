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
            $oCustomGoodsStock->orderBy("CustomGoodsStock.Seq", "ASC");
            $Temp = $oCustomGoodsStock->findAll();
            if (count($Temp) > 0) {
                // 依據關聯的"客製規格編號"，找出"客製規格"及""客製規格分類"資料
                $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
                foreach ($Temp as $key=>$Data) {
                    $CustomSpecIDArray = explode(",", $Temp[$key]["CustomSpecID"]);
                    if (count($CustomSpecIDArray)>0) {
                        $oSpec->resetQuery();
                        $oSpec->select("CustomGoodsSpec.CustomSpecID");
                        $oSpec->select("CustomGoodsSpec.Title AS SpecTitle");
                        $oSpec->select("CustomGoodsSpec.Status AS SpecStatus");
                        $oSpec->select("CustomGoodsSpec.Seq AS SpecSeq");
                        $oSpec->select("CustomGoodsSpec.SpecCategoryID");
                        $oSpec->select("CustomGoodsSpecCategory.Title AS SpecCategoryTitle");
                        $oSpec->select("CustomGoodsSpecCategory.Status AS SpecCategoryStatus");
                        $oSpec->select("CustomGoodsSpecCategory.Seq AS SpecCategorySeq");
                        $oSpec->join("CustomGoodsSpecCategory", "CustomGoodsSpecCategory.SpecCategoryID=CustomGoodsSpec.SpecCategoryID");
                        $oSpec->whereIn("CustomSpecID", $CustomSpecIDArray);
                        $oSpec->orderBy("CustomGoodsSpec.Seq");
                        $oSpec->orderBy("CustomGoodsSpecCategory.Seq");
                        $oSpec->orderBy("CustomSpecID", "DESC");
                        $Temp[$key]["SpecArray"] = $oSpec->findAll();
                    } else {
                        $Temp[$key]["SpecArray"] = [];
                    }
                }

                $CustomGoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
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
//            $List[$key]["Picture"] = $GoodsPictureKeyValue[$List[$key]["GoodsID"]]??[];
        }

        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getData($GoodsID)
    {
        //商品ID
        $oGoods = new \App\Models\Goods\Goods();
        $oGoods->select("Goods.*,SUM(GoodsStock.Stock) AS StockSum");
        $oGoods->join("GoodsStock", "GoodsStock.GoodsID=Goods.GoodsID", "left");
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
        $oCustomGoodsStock->orderBy("CustomGoodsStock.Seq", "ASC");
        $Temp = $oCustomGoodsStock->findAll();
        if (count($Temp) > 0) {
            // 依據關聯的"客製規格編號"，找出"客製規格"及""客製規格分類"資料
            $oSpec = new \App\Models\CustomGoods\CustomGoodsSpec();
            foreach ($Temp as $key=>$tmpData) {
                $CustomSpecIDArray = explode(",", $Temp[$key]["CustomSpecID"]);
                if (count($CustomSpecIDArray)>0) {
                    $oSpec->resetQuery();
                    $oSpec->select("CustomGoodsSpec.CustomSpecID");
                    $oSpec->select("CustomGoodsSpec.Title AS SpecTitle");
                    $oSpec->select("CustomGoodsSpec.Status AS SpecStatus");
                    $oSpec->select("CustomGoodsSpec.Seq AS SpecSeq");
                    $oSpec->select("CustomGoodsSpec.SpecCategoryID");
                    $oSpec->select("CustomGoodsSpecCategory.Title AS SpecCategoryTitle");
                    $oSpec->select("CustomGoodsSpecCategory.Status AS SpecCategoryStatus");
                    $oSpec->select("CustomGoodsSpecCategory.Seq AS SpecCategorySeq");
                    $oSpec->join("CustomGoodsSpecCategory", "CustomGoodsSpecCategory.SpecCategoryID=CustomGoodsSpec.SpecCategoryID");
                    $oSpec->whereIn("CustomSpecID", $CustomSpecIDArray);
                    $oSpec->orderBy("CustomGoodsSpec.Seq");
                    $oSpec->orderBy("CustomGoodsSpecCategory.Seq");
                    $oSpec->orderBy("CustomSpecID", "DESC");
                    $Temp[$key]["SpecArray"] = $oSpec->findAll();
                } else {
                    $Temp[$key]["SpecArray"] = [];
                }
            }

            $CustomGoodsStockKeyValue = \App\Libraries\Tools\DatabaseTools::ListToKVMultiple($Temp, "GoodsID");
        }

        //放入資料
        $Data["Menu"]     = $Menu2GoodsKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Stock"]    = $GoodsStockKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Picture"]  = $GoodsPictureKeyValue[$Data["GoodsID"]] ?? [];
        $Data["Discount"] = $GoodsDiscountKeyValue[$Data["GoodsID"]] ?? [];
        $Data["CustomGoodsStock"] = $CustomGoodsStockKeyValue[$Data["GoodsID"]] ?? [];
        //Res
        return $this->respond(ResponseData::success($Data));
    }
}
