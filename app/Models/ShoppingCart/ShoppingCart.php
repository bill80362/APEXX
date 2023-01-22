<?php

namespace App\Models\ShoppingCart;

use CodeIgniter\Model;

class ShoppingCart extends Model
{
    protected $table      = 'ShoppingCart';
    protected $primaryKey = 'ShoppingCartID';

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
        "MemberID" => "required|numeric",
        "GoodsID" => "required|numeric",
        "ColorID" => "required|numeric",
        "SizeID" => "required|numeric",
    ];
    protected $validationMessages = [
        "MemberID" => [
            "required" => "會員ID為必填",
            "numeric" => "會員ID是整數",
        ],
        "GoodsID" => [
            "required" => "商品ID為必填",
            "numeric" => "商品ID必須是整數",
        ],
        "ColorID" => [
            "required" => "顏色ID為必填",
            "numeric" => "顏色ID必須是整數",
        ],
        "SizeID" => [
            "required" => "尺寸ID為必填",
            "numeric" => "尺寸ID必須是整數",
        ],
    ];
    protected $skipValidation     = false;
}