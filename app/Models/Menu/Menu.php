<?php

namespace App\Models\Menu;

use CodeIgniter\Model;

class Menu extends Model
{
    protected $table      = 'Menu';
    protected $primaryKey = 'MenuID';

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
        "MenuCategoryID" => "required|numeric",
        "Seq" => "required|numeric",
        "Title" => "required",
        "Status" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "MenuCategoryID" => [
            "required" => "目錄ID為必填",
            "numeric" => "目錄ID是整數",
        ],
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