<?php

namespace App\Models\News;

use CodeIgniter\Model;

class News extends Model
{
    protected $table      = 'News';
    protected $primaryKey = 'NewsID';

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
        "NewsCategoryID" => "required|numeric",
        "Seq" => "required|numeric",
        "Title" => "required",
    ];
    protected $validationMessages = [
        "NewsCategoryID" => [
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
    ];
    protected $skipValidation     = false;
}