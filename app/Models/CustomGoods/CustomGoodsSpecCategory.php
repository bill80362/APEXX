<?php

namespace App\Models\CustomGoods;

use CodeIgniter\Model;

class CustomGoodsSpecCategory extends Model
{
    protected $table      = 'CustomGoodsSpecCategory';
    protected $primaryKey = 'SpecCategoryID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "GoodsID" => "required|numeric",
        "Seq" => "required|numeric",
        "Title" => "required",
        "Status" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "GoodsID" => [
            "required" => "商品編號為必填",
            "numeric" => "商品編號必須是整數",
        ],
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
        "Title" => [
            "required" => "名稱為必填",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
    ];
    protected $skipValidation     = false;
}
