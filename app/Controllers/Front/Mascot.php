<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Mascot extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oMascot = new \App\Models\Mascot\Mascot();
        $oMascot->orderBy("Mascot.Seq","ASC");
        $List = $oMascot->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
