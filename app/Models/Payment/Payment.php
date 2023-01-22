<?php

namespace App\Models\Payment;

use CodeIgniter\Model;

class Payment extends Model
{
    protected $table      = 'Payment';
    protected $primaryKey = 'PaymentID';

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
        "DeliveryFrozen" => "in_list[Y,N]",
        "Status" => "in_list[Y,N]",
        "ChargePercent" => "required|numeric",
        "ChargeFee" => "required|numeric",
    ];
    protected $validationMessages = [
        "DeliveryFrozen" =>[
            "in_list"=> "低溫是否可用只能是Y,N",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
        "ChargePercent" => [
            "required" => "手續費百分比為必填",
            "numeric" => "手續費百分比是整數",
        ],
        "ChargeFee" => [
            "required" => "手續費金額為必填",
            "numeric" => "手續費金額是整數",
        ],
    ];
    protected $skipValidation     = false;
}