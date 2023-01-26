<?php

namespace App\Models\Menu2Goods;

use CodeIgniter\Model;

class Menu2Goods extends Model
{
    protected $table      = 'Menu2Goods';
    protected $primaryKey = 'MenuID';

    protected $useAutoIncrement = false;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "MenuID" => "required|numeric",
        "GoodsID" => "required|numeric",
    ];
    protected $validationMessages = [
        "MenuID" => [
            "required" => "選單ID為必填",
            "numeric" => "選單ID是整數",
        ],
        "GoodsID" => [
            "required" => "商品ID為必填",
            "numeric" => "商品ID是整數",
        ],
    ];
    protected $skipValidation     = false;
}
