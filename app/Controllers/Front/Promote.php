<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Promote extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oPromote = new \App\Models\Promote\Promote();
        $oPromote->orderBy("Promote.Seq","ASC");
        $List = $oPromote->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
