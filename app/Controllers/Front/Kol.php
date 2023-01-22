<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Kol extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oKol = new \App\Models\Kol\Kol();
        $oKol->orderBy("Kol.Seq","ASC");
        $List = $oKol->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
