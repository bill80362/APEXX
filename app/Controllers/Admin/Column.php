<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Column extends BaseController
{
    public $ImageDirPath = "/image/column";
    use ResponseTrait;
    public function getList()
    {
        $ColumnTitleList = $this->request->getVar("Column");
        if(!is_array($ColumnTitleList)) return $this->respond(ResponseData::fail("請使用陣列格式"));
        if(count($ColumnTitleList)==0) return $this->respond(ResponseData::fail("請輸入您要查找的欄位名稱"));
        $oDataColumn = new \App\Models\DataColumn();
        $oDataColumn->whereIn("Title",$ColumnTitleList);
        $List = $oDataColumn->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function replaceData(){
        $Title = $this->request->getVar("Title");
        $Content = $this->request->getVar("Content");
        $Front = $this->request->getVar("Front");
        $oDataColumn = new \App\Models\DataColumn();
        $oDataColumn->replace([
            "Title" =>$Title,
            "Content" =>$Content,
            "Front"=>$Front
        ]);
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage(){
        $oDataColumn = new \App\Models\DataColumn();
        //上傳圖片 Image 多張
        $files = $this->request->getFiles();
        $TitleArray = [];
        foreach ($files as $Title => $file){
            if ( $file && $file->isFile()) {
                if($file->getSizeByUnit('mb')>5) return $this->respond(ResponseData::fail("檔案不能超過5MB"));
                if(!in_array($file->getMimeType(),["image/jpg","image/png","image/gif","image/jpeg","image/webp"])) return $this->respond(ResponseData::fail("檔案格式限制jpg,png,gif,jpeg,webp"));                //刪除原本圖片
                $Data = $oDataColumn->find($Title);
                if( isset($Data["Content"]) && $Data["Content"]!="" ){
                    $FileHostPath = ROOTPATH."public".$Data["Content"];
                    if(file_exists($FileHostPath))
                        unlink($FileHostPath);
                }
                //產生隨機名稱
                $name = $file->getRandomName();
                //上傳檔案
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //更新DB
                $oDataColumn->replace([
                    "Title" =>$Title,
                    "Content" =>$this->ImageDirPath."/".$name,
                    "Front"=>"Y",
                ]);
            }
        }
        //Res
        if(count($TitleArray)>0)
            $List = $oDataColumn->whereIn("Title",$TitleArray)->findAll();
        else
            $List = [];
        return $this->respond(ResponseData::success($List));
    }
}
