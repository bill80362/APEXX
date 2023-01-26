<?php

namespace App\Models\Goods;

use CodeIgniter\Model;

class Goods extends Model
{
    protected $table      = 'Goods';
    protected $primaryKey = 'GoodsID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "Seq" => "required|numeric",
        "Status" => "in_list[Y,N]",
        "CombineDiscount" => "in_list[Y,N]",
        "DeliveryFrozen" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
        "CombineDiscount" =>[
            "in_list"=> "是否為組合商品Y,N",
        ],
        "DeliveryFrozen" =>[
            "in_list"=> "是否為低溫商品Y,N",
        ],
    ];
    protected $skipValidation     = false;
}
