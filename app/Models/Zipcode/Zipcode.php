<?php

namespace App\Models\Zipcode;

use CodeIgniter\Model;
use phpDocumentor\Reflection\Types\Boolean;

class Zipcode extends Model
{
    protected $table      = 'Zipcode';
    protected $primaryKey = 'ZipcodeID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [

    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    //是否離島縣市
    public function isOutlying(string $CityName):bool
    {
        if(in_array($CityName,["南海諸島","澎湖縣","金門縣","連江縣"])){
            return true;
        }
        return false;
    }

}