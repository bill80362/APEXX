<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Carousel extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oCarousel = new \App\Models\Carousel\Carousel();
        $oCarousel->orderBy("Carousel.Seq","ASC");
        $List = $oCarousel->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
