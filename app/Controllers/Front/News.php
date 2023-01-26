<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class News extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oNews = new \App\Models\News\News();
        $oNews->select("News.*");
        $oNews->select("NewsCategory.Title AS CategoryTitle");
        $oNews->join("NewsCategory", "NewsCategory.NewsCategoryID=News.NewsCategoryID");
        $oNews->orderBy("NewsCategory.Seq", "ASC");
        $oNews->orderBy("News.Seq", "ASC");
        $List = $oNews->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList()
    {
        $oCategory = new \App\Models\News\Category();
        $oCategory->orderBy("Seq", "ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
