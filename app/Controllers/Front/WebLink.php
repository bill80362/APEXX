<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class WebLink extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oWebLink = new \App\Models\WebLink\WebLink();
        $oWebLink->select("WebLink.*");
        $oWebLink->select("WebLinkCategory.Title AS CategoryTitle");
        $oWebLink->join("WebLinkCategory", "WebLinkCategory.WebLinkCategoryID=WebLink.WebLinkCategoryID");
        $oWebLink->orderBy("WebLinkCategory.Seq", "ASC");
        $oWebLink->orderBy("WebLink.Seq", "ASC");
        $List = $oWebLink->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList()
    {
        $oCategory = new \App\Models\WebLink\Category();
        $oCategory->where("Status", "Y");
        $oCategory->orderBy("Seq", "ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
