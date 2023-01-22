<?php

namespace App\Models\Favorite;

use CodeIgniter\Model;

class Favorite extends Model
{
    protected $table      = 'Favorite';
    protected $primaryKey = 'FavoriteID';

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
        "FavoriteGoodsID" => "required|numeric",
    ];
    protected $validationMessages = [
        "MemberID" => [
            "required" => "會員ID為必填",
            "numeric" => "會員ID是整數",
        ],
        "FavoriteGoodsID" => [
            "required" => "商品ID為必填",
            "numeric" => "商品ID必須是整數",
        ],
    ];
    protected $skipValidation     = false;
}