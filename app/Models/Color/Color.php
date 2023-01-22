<?php

namespace App\Models\Color;

use CodeIgniter\Model;

class Color extends Model
{
    protected $table      = 'Color';
    protected $primaryKey = 'ColorID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "ColorTitle" => "required",
    ];
    protected $validationMessages = [
        "ColorTitle" => [
            "required" => "名稱為必填",
        ],
    ];
    protected $skipValidation     = false;
}