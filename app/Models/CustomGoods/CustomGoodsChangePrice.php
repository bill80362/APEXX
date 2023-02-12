<?php

namespace App\Models\CustomGoods;

use CodeIgniter\Model;

class CustomGoodsChangePrice extends Model
{
    protected $table      = 'CustomGoodsChangePrice';
    protected $primaryKey = 'ChangePriceID';

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
        "CustomSpecID" => "required",
        "ChangePrice" => "required|numeric",
    ];
    protected $validationMessages = [
        "GoodsID" => [
            "required" => "商品編號為必填",
            "numeric" => "商品編號必須是整數",
        ],
        "CustomSpecID" => [
            "required" => "客製規格編號為必填",
        ],
        "ChangePrice" => [
            "required" => "規格組合異動價為必填",
            "numeric" => "規格組合異動價必須是整數",
        ],
    ];
    protected $skipValidation     = false;
}
