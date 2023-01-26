<?php

namespace App\Models;

use CodeIgniter\Model;

class DataColumn extends Model
{
    protected $table      = 'DataColumn';
    protected $primaryKey = 'Title';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [

    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "Front" => "in_list[Y,N]",
    ];
    protected $validationMessages = [
        "Front" =>[
            "in_list"=> "是否前端能讀取只能是Y,N",
        ],
    ];
    protected $skipValidation     = false;
}
