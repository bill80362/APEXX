<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Video extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        $oVideo = new \App\Models\Video\Video();
        $oVideo->select("Video.*");
        $oVideo->select("VideoCategory.Title AS CategoryTitle");
        $oVideo->join("VideoCategory", "VideoCategory.VideoCategoryID=Video.VideoCategoryID");
        $oVideo->orderBy("VideoCategory.Seq", "ASC");
        $oVideo->orderBy("Video.Seq", "ASC");
        $List = $oVideo->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList()
    {
        $oCategory = new \App\Models\Video\Category();
        $oCategory->orderBy("Seq", "ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
