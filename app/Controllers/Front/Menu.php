<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Menu extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oMenu = new \App\Models\Menu\Menu();
        $oMenu->select("Menu.*");
        $oMenu->select("MenuCategory.Title AS CategoryTitle");
        $oMenu->join("MenuCategory", "MenuCategory.MenuCategoryID=Menu.MenuCategoryID");
        $oMenu->orderBy("MenuCategory.Seq", "ASC");
        $oMenu->orderBy("Menu.Seq", "ASC");
        $List = $oMenu->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList()
    {
        $oCategory = new \App\Models\Menu\Category();
        $oCategory->where("Status", "Y");
        $oCategory->orderBy("Seq", "ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
