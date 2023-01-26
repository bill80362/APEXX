<?php

namespace App\Models\Discount;

use CodeIgniter\Model;

class Discount extends Model
{
    protected $table      = 'Discount';
    protected $primaryKey = 'DiscountID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public static $DiscountTypeTxt = [
        "P"=>"打折",
        "G"=>"贈品",
        "D"=>"免運",
    ];

    protected $validationRules    = [
        "DiscountType" => "in_list[P,G,D]",
        "Combine" => "in_list[Y,N]",
        "LimitMember" => "in_list[Y,N]",
        "Threshold" => "required|numeric",
        "StartTime" => "required|valid_date[Y-m-d H:i:s]",
        "EndTime" => "required|valid_date[Y-m-d H:i:s]",
        "Status" => "in_list[Y,N]",
        "Title" => "required",
    ];
    protected $validationMessages = [
        "DiscountType" => [
            "in_list" => "優惠類型只能是P,G,D",
        ],
        "Combine" => [
            "in_list" => "組合優惠Y或N",
        ],
        "LimitMember" => [
            "in_list" => "組合優惠Y或N",
        ],
        "Threshold" => [
            "required" => "優惠門檻為必填",
            "numeric" => "優惠門檻必須是整數",
        ],
        "StartTime" => [
            "required" => "優惠起始時間為必填",
            "valid_date" => "優惠起始時間格式為2011-11-11 11:11;11",
        ],
        "EndTime" => [
            "required" => "優惠結束時間為必填",
            "valid_date" => "優惠結束時間格式為2011-11-11 11:11;11",
        ],
        "Status" => [
            "in_list" => "組合優惠Y或N",
        ],
        "Title" => [
            "required" => "優惠名稱必填",
        ],
    ];
    protected $skipValidation     = false;
}
