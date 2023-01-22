<?php

namespace App\Models\Menu;

use CodeIgniter\Model;

class Category extends Model
{
    protected $table      = 'MenuCategory';
    protected $primaryKey = 'MenuCategoryID';

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
        "Seq" => "required|numeric",
        "Title" => "required",
        "Status" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
        "Title" => [
            "required" => "標題為必填",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
    ];
    protected $skipValidation     = false;
}