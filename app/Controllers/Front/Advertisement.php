<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Advertisement extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oAdvertisement = new \App\Models\Advertisement\Advertisement();
        $oAdvertisement->select("Advertisement.*");
        $oAdvertisement->select("AdvertisementCategory.Title AS CategoryTitle");
        $oAdvertisement->join("AdvertisementCategory", "AdvertisementCategory.AdvertisementCategoryID=Advertisement.AdvertisementCategoryID");
        $oAdvertisement->orderBy("AdvertisementCategory.Seq", "ASC");
        $oAdvertisement->orderBy("Advertisement.Seq", "ASC");
        $List = $oAdvertisement->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList()
    {
        $oCategory = new \App\Models\Advertisement\Category();
        $oCategory->orderBy("Seq", "ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
