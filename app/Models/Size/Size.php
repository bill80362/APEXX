<?php

namespace App\Models\Size;

use CodeIgniter\Model;

class Size extends Model
{
    protected $table      = 'Size';
    protected $primaryKey = 'SizeID';

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
        "SizeTitle" => "required",
    ];
    protected $validationMessages = [
        "SizeTitle" => [
            "required" => "名稱為必填",
        ],
    ];
    protected $skipValidation     = false;
}