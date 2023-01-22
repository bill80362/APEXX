<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class Homepage extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $List = [];
        //
        $oController = new \App\Controllers\Front\Carousel();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["Carousel"] = json_decode($Response->getBody(),true);
        //
        $oController = new \App\Controllers\Front\News();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["News"] = json_decode($Response->getBody(),true);
        $Response = $oController->getCategoryList();
        $List["NewsCategory"] = json_decode($Response->getBody(),true);
        //
        $oController = new \App\Controllers\Front\Promote();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["Promote"] = json_decode($Response->getBody(),true);
        //
        $oController = new \App\Controllers\Front\Column();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["Column"] = json_decode($Response->getBody(),true);
        //
        $oController = new \App\Controllers\Front\Menu();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["Menu"] = json_decode($Response->getBody(),true);
        $Response = $oController->getCategoryList();
        $List["MenuCategory"] = json_decode($Response->getBody(),true);
        //
        $oController = new \App\Controllers\Front\Goods();
        $oController->initController(Services::request(),Services::response(),Services::logger());
        $Response = $oController->getList();
        $List["Goods"] = json_decode($Response->getBody(),true);
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
