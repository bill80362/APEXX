<?php

namespace App\Controllers\Admin\Kol;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Kol extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/kol";
    public function getList()
    {
        //
        $oKol = new \App\Models\Kol\Kol();
        $oKol->orderBy("Seq");
        $oKol->orderBy("KolID", "DESC");
        $List = $oKol->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        $SubTitle = $this->request->getVar("SubTitle");
        $Description = $this->request->getVar("Description");
        //
        $oKol = new \App\Models\Kol\Kol();
        $oKol->protect(false);
        $KolID = $oKol->insert([
            "Image1"=>$Image1,
            "Image2"=>$Image2,
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
            "SubTitle"=>$SubTitle,
            "Description"=>$Description,
        ]);
        if ($oKol->errors()) {
            $ErrorMsg = implode(",", $oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oKol->find($KolID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $Image1 = $this->request->getVar("Image1");
        $Image2 = $this->request->getVar("Image2");
        $Seq = $this->request->getVar("Seq");
        $Link = $this->request->getVar("Link");
        $Title = $this->request->getVar("Title");
        $SubTitle = $this->request->getVar("SubTitle");
        $Description = $this->request->getVar("Description");
        //
        $oKol = new \App\Models\Kol\Kol();
        //??????ID
        $Data = $oKol->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //????????????
        $oKol->protect(false);
        $updateData = [
            "Seq"=>$Seq,
            "Link"=>$Link,
            "Title"=>$Title,
            "SubTitle"=>$SubTitle,
            "Description"=>$Description,
        ];
        if ($Image1!==null) {
            //??????????????????
            if (isset($Data["Image1"]) && $Data["Image1"]!="") {
                $FileHostPath = ROOTPATH."public".$Data["Image1"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //????????????
            $updateData["Image1"] = $Image1;
        }
        if ($Image2!==null) {
            //??????????????????
            if (isset($Data["Image2"]) && $Data["Image2"]!="") {
                $FileHostPath = ROOTPATH."public".$Data["Image2"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //????????????
            $updateData["Image2"] = $Image2;
        }
        $oKol->update($ID, $updateData);
        if ($oKol->errors()) {
            $ErrorMsg = implode(",", $oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oKol->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oKol = new \App\Models\Kol\Kol();
        //??????ID
        $Data = $oKol->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //??????????????????
        if (isset($Data["Image1"]) && $Data["Image1"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image1"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //??????????????????
        if (isset($Data["Image2"]) && $Data["Image2"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image2"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //??????DB
        $oKol->protect(false);
        $oKol->delete($ID);
        if ($oKol->errors()) {
            $ErrorMsg = implode(",", $oKol->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oKol = new \App\Models\Kol\Kol();
        //??????ID
        $Data = $oKol->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //???????????? Image ??????
        for ($i=1;$i<=2;$i++) {
            $file = $this->request->getFile('Image'.$i);
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb')>5) {
                    return $this->respond(ResponseData::fail("??????????????????5MB"));
                }
                if (!in_array($file->getMimeType(), ["image/jpg","image/png","image/gif","image/jpeg","image/webp"], true)) {
                    return $this->respond(ResponseData::fail("??????????????????jpg,png,gif,jpeg,webp"));
                }                //??????????????????
                if (isset($Data["Image".$i]) && $Data["Image".$i]!="") {
                    $FileHostPath = ROOTPATH."public".$Data["Image".$i];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
                //??????????????????
                $name = $file->getRandomName();
                //????????????
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //??????DB
                $oKol->resetQuery();
                $oKol->protect(false);
                $oKol->update($ID, ["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oKol->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function updateSeqBatch()
    {
        $SeqArray = $this->request->getVar();
        if (!is_array($SeqArray)) {
            return $this->respond(ResponseData::fail("??????????????????"));
        }
        //????????????
        $oKol = new \App\Models\Kol\Kol();
        $oKol->protect(false);
        foreach ($SeqArray as $key=>$Data) {
            $oKol->update($Data->ID, ["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
