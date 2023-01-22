<?php

namespace App\Models\Coupon;

use CodeIgniter\Model;

class Coupon extends Model
{
    protected $table      = 'Coupon';
    protected $primaryKey = 'CouponID';

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
        "CouponNumber" => "required",
        "Title" => "required",
        "Money" => "required|numeric",
        "LimitMember" => "in_list[Y,N]",
        "OnlyMember" => "in_list[Y,N]",
        "Threshold" => "required|numeric",
        "StartTime" => "required|valid_date[Y-m-d H:i:s]",
        "EndTime" => "required|valid_date[Y-m-d H:i:s]",
        "Status" => "in_list[Y,N]",
        "CouponCount" => "required|numeric",
    ];
    protected $validationMessages = [
        "CouponNumber" => [
            "required" => "序號為必填",
        ],
        "Title" => [
            "required" => "名稱為必填",
        ],
        "Money" => [
            "required" => "折價金額為必填",
            "numeric" => "折價金額必須是整數",
        ],
        "LimitMember" => [
            "in_list" => "限制會員Y或N",
        ],
        "OnlyMember" => [
            "in_list" => "指定會員Y或N",
        ],
        "Threshold" => [
            "required" => "優惠門檻為必填",
            "numeric" => "優惠門檻必須是整數",
        ],
        "StartTime" => [
            "required" => "優惠起始時間為必填",
            "valid_date" => "優惠起始時間格式為2011-11-11 11:11;11",
        ],
        "EndTime" => [
            "required" => "優惠結束時間為必填",
            "valid_date" => "優惠結束時間格式為2011-11-11 11:11;11",
        ],
        "Status" => [
            "in_list" => "組合優惠Y或N",
        ],
        "CouponCount" => [
            "required" => "優惠卷數量為必填",
            "numeric" => "優惠卷數量必須是整數",
        ],
    ];
    protected $skipValidation     = false;

    //增減數量
    public function ioCount(int $CouponID ,int $fixCount){
        $this->resetQuery();
        //增加或是減少
        if($fixCount==0){
            return true;
        }elseif($fixCount>0){
            $this->set("CouponCount","CouponCount+".abs($fixCount),false);
        }elseif($fixCount<0){
            $this->set("CouponCount","CouponCount-".abs($fixCount),false);
        }
        //
        $this->where("CouponID",$CouponID);
        $this->update();
        return true;
    }
}