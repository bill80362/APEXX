<?php

namespace App\Models\Carousel;

use CodeIgniter\Model;

class Carousel extends Model
{
    protected $table      = 'Carousel';
    protected $primaryKey = 'CarouselID';

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
    ];
    protected $validationMessages = [
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
    ];
    protected $skipValidation     = false;
}
