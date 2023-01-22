<?php

namespace App\Models\Receiver;

use CodeIgniter\Model;

class Receiver extends Model
{
    protected $table      = 'Receiver';
    protected $primaryKey = 'ReceiverID';

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
        "Seq" => "required|numeric",
        "Name" => "required",
        "Zip" => "required|numeric",
        "Address" => "required",
        "Phone" => "required|max_length[15]",
    ];
    protected $validationMessages = [
        "MemberID" => [
            "required" => "會員ID為必填",
            "numeric" => "會員ID是整數",
        ],
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
        "Name" => [
            "required" => "名稱為必填",
        ],
        "Zip" => [
            "required" => "郵遞區號為必填",
            "numeric" => "郵遞區號必須是整數",
        ],
        "Address" => [
            "required" => "地址為必填",
        ],
        "Phone" => [
            "required" => "電話為必填",
            "max_length"=>"最多15碼",
        ],
    ];
    protected $skipValidation     = false;
}