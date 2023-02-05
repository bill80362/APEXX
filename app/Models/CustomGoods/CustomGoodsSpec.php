<?php

namespace App\Models\CustomGoods;

use CodeIgniter\Model;

class CustomGoodsSpec extends Model
{
    protected $table      = 'CustomGoodsSpec';
    protected $primaryKey = 'CustomSpecID';

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
        "SpecCategoryID" => "required|numeric",
        "Seq" => "required|numeric",
        "Title" => "required",
        "Status" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "SpecCategoryID" => [
            "required" => "客製規格類別ID為必填",
            "numeric" => "客製規格類別ID必須是整數",
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
