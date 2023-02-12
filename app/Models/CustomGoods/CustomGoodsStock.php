<?php

namespace App\Models\CustomGoods;

use CodeIgniter\Model;

class CustomGoodsStock extends Model
{
    protected $table      = 'CustomGoodsStock';
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
        "Status" => "in_list[Y,N]",
        "DeliverVolume" => "required|numeric",
        "DeliverWeight" => "required|numeric",
    ];
    protected $validationMessages = [
        "GoodsID" => [
            "required" => "商品編號為必填",
            "numeric" => "商品編號必須是整數",
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
    ];
    protected $skipValidation     = false;

    //增減庫存
    public function ioStock(int $GoodsID, int $fixCount)
    {
        $this->protect(false);
        $this->resetQuery();
        //增加或是減少
        if ($fixCount==0) {
            return true;
        } elseif ($fixCount>0) {
            $this->set("Stock", "Stock+".abs($fixCount), false);
        } elseif ($fixCount<0) {
            $this->set("Stock", "Stock-".abs($fixCount), false);
        }
        //
        $this->where("GoodsID", $GoodsID);
        $this->update();
        $this->protect(true);
        return true;
    }
}
