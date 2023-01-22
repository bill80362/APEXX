<?php

namespace App\Models\Goods;

use CodeIgniter\Model;

class GoodsStock extends Model
{
    protected $table      = 'GoodsStock';
    protected $primaryKey = 'GoodsID';

    protected $useAutoIncrement = false;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [

    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        "GoodsID" => "required|numeric",
        "ColorID" => "required|numeric",
        "SizeID" => "required|numeric",
        "Status" => "in_list[Y,N]",
        "DeliverVolume" => "required|numeric",
        "DeliverWeight" => "required|numeric",
        "Seq" => "required|numeric",
    ];
    protected $validationMessages = [
        "GoodsID" => [
            "required" => "商品編號為必填",
            "numeric" => "商品編號必須是整數",
        ],
        "ColorID" => [
            "required" => "顏色編號為必填",
            "numeric" => "顏色編號必須是整數",
        ],
        "SizeID" => [
            "required" => "尺寸編號為必填",
            "numeric" => "尺寸編號必須是整數",
        ],
        "Status" =>[
            "in_list"=> "開關只能是Y,N",
        ],
        "DeliverVolume" => [
            "required" => "體積為必填",
            "numeric" => "體積必須是整數",
        ],
        "DeliverWeight" => [
            "required" => "重量為必填",
            "numeric" => "重量必須是整數",
        ],
        "Seq" => [
            "required" => "排序權重為必填",
            "numeric" => "排序權重必須是整數",
        ],
    ];
    protected $skipValidation     = false;

    //增減庫存
    public function ioStock(int $GoodsID,int $ColorID,int $SizeID,int $fixCount){
        $this->protect(false);
        $this->resetQuery();
        //增加或是減少
        if($fixCount==0){
            return true;
        }elseif($fixCount>0){
            $this->set("Stock","Stock+".abs($fixCount),false);
        }elseif($fixCount<0){
            $this->set("Stock","Stock-".abs($fixCount),false);
        }
        //
        $this->where("GoodsID",$GoodsID);
        $this->where("ColorID",$ColorID);
        $this->where("SizeID",$SizeID);
        $this->update();
        $this->protect(true);
        return true;
    }

}