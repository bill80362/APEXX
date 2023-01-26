<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Shipping extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oShipping = new \App\Models\Shipping\Shipping();
        $oShipping->where("Status", "Y");
        $oShipping->orderBy("ShippingID", "DESC");
        $List = $oShipping->findAll();
        foreach ($List as $key=>$value) {
            if (in_array($value["ShippingType"], $oShipping->ShippingIsPayment, true)) {
                $List[$key]["ShippingIsPayment"] = true;
            } else {
                $List[$key]["ShippingIsPayment"] = false;
            }
        }
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
