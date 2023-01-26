<?php

namespace App\Models\Member;

use CodeIgniter\Model;

class MemberToken extends Model
{
    protected $table      = 'MemberToken';
    protected $primaryKey = 'MemberTokenID';

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
}
