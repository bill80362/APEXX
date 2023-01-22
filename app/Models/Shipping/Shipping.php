<?php

namespace App\Models\Shipping;

use CodeIgniter\Model;

class Shipping extends Model
{
    protected $table      = 'Shipping';
    protected $primaryKey = 'ShippingID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public $ShippingType = [
        "FAMIC2C" => "全家店到店",
        "UNIMARTC2C" => "7-ELEVEN超商交貨便",
        "TCAT" => "黑貓",
//        "TCATL" => "黑貓低溫宅配",
        "HCT" => "新竹物流",
    ];
    //貨到付款，兩者需相同
    public $ShippingIsPayment = [
        "FAMIC2C","UNIMARTC2C"
    ];

    protected $validationRules    = [
        "ShippingType" => "in_list[FAMIC2C,UNIMARTC2C,TCAT,HCT]",
        "Status" => "in_list[Y,N]",
        "DeliveryFrozen" => "in_list[Y,N]",
        "StatusOutlying" => "in_list[Y,N]",
        "ShippingFee" => "required|numeric",
        "ShippingFeeOutlying" => "required|numeric",
        "DeliverVolumeMax" => "required|numeric",
        "DeliverWeightMax" => "required|numeric",
    ];
    protected $validationMessages = [
        "DeliveryFrozen" =>[
            "in_list"=> "低溫是否可用只能是Y,N",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
        "StatusOutlying" => [
            "in_list"=> "離島關開只能是Y,N",
        ],
        "ShippingFee" => [
            "required" => "本島運費為必填",
            "numeric" => "本島運費是整數",
        ],
        "ShippingFeeOutlying" => [
            "required" => "離島運費為必填",
            "numeric" => "離島運費是整數",
        ],
        "DeliverVolumeMax" => [
            "required" => "體積最大額度為必填",
            "numeric" => "體積最大額度是整數",
        ],
        "DeliverWeightMax" => [
            "required" => "重量最大額度為必填",
            "numeric" => "重量最大額度是整數",
        ],
    ];
    protected $skipValidation     = false;
}