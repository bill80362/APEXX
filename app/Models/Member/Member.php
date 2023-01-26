<?php

namespace App\Models\Member;

use CodeIgniter\Model;

class Member extends Model
{
    protected $table      = 'Member';
    protected $primaryKey = 'MemberID';

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
//        "Account" => "required|alpha_numeric|min_length[4]|is_unique[Member.Account]",
        "Account" => "required|valid_email|is_unique[Member.Account]",
        "Password" => "required|alpha_numeric|min_length[4]",
        "Status" => "in_list[Y,N,W]",
        "Name" => "required",
        "Phone" => "required|numeric|is_unique[Member.Phone]",
//        "Email" => "required|valid_email|is_unique[Member.Email]",
        "Birthday" => "required|valid_date[Y-m-d]",
        "Sex" => "in_list[M,F]",
    ];
    protected $validationMessages = [
        "Account" => [
            "required" => "帳號為必填",
            "is_unique"=>"此帳號已被使用過",
//            "alpha_numeric"=>"只能是字母、數字",
//            "min_length"=>"最少四碼",
            "valid_email"=>"必須是Email格式",
        ],
        "Password" => [
            "required" => "帳號為必填",
            "alpha_numeric"=>"只能是字母、數字",
            "min_length"=>"最少四碼",
        ],
        "Status" =>[
            "in_list"=> "Y或是N",
        ],
        "Name" => [
            "required" => "排序權重為必填",
        ],
        "Phone" => [
            "required" => "排序權重為必填",
            "is_unique"=>"此電話已被使用過",
            "numeric"=>"只能是數字",
        ],
//        "Email" => [
//            "required" => "排序權重為必填",
//            "valid_email"=>"必須是Email格式",
//            "is_unique"=>"此Email已被使用過",
//        ],
        "Birthday" => [
            "required" => "標題為必填",
            "valid_date"=>"日期格式2000-11-11",
        ],
        "Sex" =>[
            "in_list"=> "性別是M或F",
        ],
    ];
    protected $skipValidation     = false;
}
