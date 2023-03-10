<?php

namespace App\Libraries;

class CustomGoods
{
    // 依據"客製規格編號"或"客製規格分類編號"，找出"客製規格和分類列表"資料
    public static function findCustomSpecList(array $GoodsIDArray, array $SpecCategoryIDArray, array $CustomSpecIDArray)
    {
        if (!isset($GoodsIDArray) || $GoodsIDArray == "" || count($GoodsIDArray) == 0) {
            return [];
        }

        $db = \Config\Database::connect();
        $builder = $db->table("CustomGoodsSpecCategory");

        $builder->resetQuery();
        $builder->select("CustomGoodsSpecCategory.GoodsID");
        $builder->select("CustomGoodsSpecCategory.SpecCategoryID");
        $builder->select("CustomGoodsSpecCategory.Title AS `SpecCategoryTitle`");
        $builder->select("CustomGoodsSpecCategory.Status AS `SpecCategoryStatus`");
        $builder->select("CustomGoodsSpecCategory.Seq AS `SpecCategorySeq`");
        $builder->select("CustomGoodsSpec.CustomSpecID");
        $builder->select("CustomGoodsSpec.Title AS `SpecTitle`");
        $builder->select("CustomGoodsSpec.Status AS `SpecStatus`");
        $builder->select("CustomGoodsSpec.Seq AS `SpecSeq`");
        $builder->join("CustomGoodsSpec", "CustomGoodsSpecCategory.SpecCategoryID = CustomGoodsSpec.SpecCategoryID");
        $builder->whereIn('CustomGoodsSpecCategory.GoodsID', $GoodsIDArray);
        if (count($SpecCategoryIDArray)>0) {
            $builder->whereIn('CustomGoodsSpecCategory.SpecCategoryID', $SpecCategoryIDArray);
        }
        if (count($CustomSpecIDArray)>0) {
            $builder->whereIn('CustomGoodsSpec.CustomSpecID', $CustomSpecIDArray);
        }
        $builder->orderBy('CustomGoodsSpecCategory.GoodsID, CustomGoodsSpecCategory.Seq, CustomGoodsSpecCategory.SpecCategoryID, CustomGoodsSpec.Seq, CustomGoodsSpec.CustomSpecID');

        return $builder->get()->getResultArray();
    }
}
