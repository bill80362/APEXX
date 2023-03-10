<?php

namespace App\Models\Goods;

use CodeIgniter\Model;

class GoodsPicture extends Model
{
    protected $table      = 'GoodsPicture';
    protected $primaryKey = 'GoodsPictureID';

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
        "ColorID" => "required|numeric",
        "SizeID" => "required|numeric",
        "Seq" => "required|numeric",
        "Image" => "required",
    ];
    protected $validationMessages = [
        "GoodsID" => [
            "required" => "商品編號為必填",
            "numeric" => "商品編號必須是整數",
        ],
        "ColorID" => [
            "required" => "顏色編號為必填",
            "numeric" => "顏色編號必須是整數",
        ],
        "SizeID" => [
            "required" => "尺寸編號為必填",
            "numeric" => "尺寸編號必須是整數",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
        "Image" =>[
            "required"=> "圖片路徑為必填",
        ],
    ];
    protected $skipValidation     = false;
}
