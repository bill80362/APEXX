<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Column extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oDataColumn = new \App\Models\DataColumn();
        $oDataColumn->where("Front", "Y");
        $List = $oDataColumn->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
