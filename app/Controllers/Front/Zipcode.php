<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Zipcode extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oZipcode = new \App\Models\Zipcode\Zipcode();
        $List = $oZipcode->findAll();
        return $this->respond(ResponseData::success($List));
    }
}
