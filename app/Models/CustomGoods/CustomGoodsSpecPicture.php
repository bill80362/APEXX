<?php

namespace App\Models\CustomGoods;

use CodeIgniter\Model;

class CustomGoodsSpecPicture extends Model
{
    protected $table      = 'CustomGoodsSpecPicture';
    protected $primaryKey = 'SpecPictureID';

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
        "CustomSpecID" => "required|numeric",
        "Seq" => "required|numeric",
        "Image" => "required",
    ];
    protected $validationMessages = [
        "CustomSpecID" => [
            "required" => "客製規格編號為必填",
            "numeric" => "客製規格編號必須是整數",
        ],
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
        "Image" =>[
            "required"=> "圖片路徑為必填",
        ],
    ];
    protected $skipValidation     = false;
}
